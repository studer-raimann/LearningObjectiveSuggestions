<?php namespace SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Cron;

use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Config\ConfigProvider;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Config\CourseConfigProvider;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\LearningObjective\LearningObjective;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\LearningObjective\LearningObjectiveCourse;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\LearningObjective\LearningObjectiveQuery;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Log\Log;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Notification\InternalMail;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Notification\Notification;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Notification\Parser;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Notification\Placeholders;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Notification\Sender;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Suggestion\LearningObjectiveSuggestion;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\User\User;

require_once('./Services/Cron/classes/class.ilCronJob.php');
require_once('./Services/Cron/classes/class.ilCronJobResult.php');
require_once('./Services/User/classes/class.ilObjUser.php');
require_once('./Modules/Course/classes/class.ilObjCourse.php');
require_once('./Services/AccessControl/classes/class.ilObjRole.php');

/**
 * Class SendSuggestionsCronJob
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Cron
 */
class SendSuggestionsCronJob extends \ilCronJob {

	/**
	 * @var \ilDB
	 */
	protected $db;

	/**
	 * @var ConfigProvider
	 */
	protected $config;

	/**
	 * @var Log
	 */
	protected $log;

	/**
	 * @var Parser
	 */
	protected $parser;

	/**
	 * @param \ilDB $db
	 * @param ConfigProvider $config
	 * @param Parser $parser
	 * @param Log $log
	 */
	public function __construct(\ilDB $db, ConfigProvider $config, Parser $parser, Log $log) {
		$this->db = $db;
		$this->config = $config;
		$this->parser = $parser;
		$this->log = $log;
	}


	/**
	 * @inheritdoc
	 */
	public function getId() {
		return 'alo_send_suggestions';
	}

	/**
	 * @inheritdoc
	 */
	public function getTitle() {
		return 'Lernziel-Empfehlungen versenden';
	}

	/**
	 * @inheritdoc
	 */
	public function getDescription() {
		return 'Versendet die berechneten Lernziel-Empfehlungen an Benutzer und Betreuer';
	}


	/**
	 * @inheritdoc
	 */
	public function hasAutoActivation() {
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function hasFlexibleSchedule() {
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function getDefaultScheduleType() {
		return self::SCHEDULE_TYPE_IN_MINUTES;
	}

	/**
	 * @inheritdoc
	 */
	function getDefaultScheduleValue() {
		return 60;
	}

	/**
	 * @inheritdoc
	 */
	public function run() {
		foreach ($this->config->getCourseRefIds() as $ref_id) {
			$course = new LearningObjectiveCourse(new \ilObjCourse($ref_id));
			$this->runForCourse($course);
		}
		$result = new \ilCronJobResult();
		$result->setStatus(\ilCronJobResult::STATUS_OK);
		return $result;
	}


	/**
	 * @param LearningObjectiveCourse $course
	 */
	protected function runForCourse(LearningObjectiveCourse $course) {
		$set = $this->db->query($this->getSQL($course));
		while ($row = $this->db->fetchObject($set)) {
			$this->send($course, $this->getUser($row->user_id));
		}
	}

	/**
	 * @param LearningObjectiveCourse $course
	 * @param User $user
	 */
	protected function send(LearningObjectiveCourse $course, User $user) {
		$config = new CourseConfigProvider($course);
		$query = new LearningObjectiveQuery($config);
		$placeholders = new Placeholders();
		// Note: If we can't parse the mail templates, we fail silently but write to log
		try {
			$objectives = $this->getSuggestedLearningObjectives($course, $user, $query);
			$p = $placeholders->getPlaceholders($course, $user, $objectives);
			$subject = $this->parser->parse($config->get('email_subject'), $p);
			$body = $this->parser->parse($config->get('email_body'), $p);
			$sender = new Sender($course, $user);
			$sender->subject($subject)->body($body);
			if (!$sender->send()) {
				$msg = "Failed to send learning objective suggestions for course %s and User %s";
				$this->log->write(sprintf($msg, $course->getTitle(), $user->__toString()));
			}
		} catch (\Exception $e) {
			$this->log->write("Error while trying to send learning objective suggestions: " . $e->getMessage());
			$this->log->write($e->getTraceAsString());
		}
	}

	/**
	 * @param int $user_id
	 * @return User
	 */
	protected function getUser($user_id) {
		static $cache = array();
		if (isset($cache[$user_id])) {
			return $cache[$user_id];
		}
		$user = new User(new \ilObjUser($user_id));
		$cache[$user_id] = $user;
		return $user;
	}

	/**
	 * @param LearningObjectiveCourse $course
	 * @param User $user
	 * @param LearningObjectiveQuery $query
	 * @return LearningObjective[]
	 */
	protected function getSuggestedLearningObjectives(LearningObjectiveCourse $course, User $user, LearningObjectiveQuery $query) {
		$suggestions = LearningObjectiveSuggestion::where(array(
			'user_id' => $user->getId(),
			'course_obj_id' => $course->getId(),
		))->orderBy('sort')->get();
		$objectives = array();
		foreach ($suggestions as $suggestion) {
			/** @var $suggestion LearningObjectiveSuggestion */
			$objectives[] = $query->getByObjectiveId($suggestion->getObjectiveId());
		}
		return $objectives;
	}


	/**
	 * @param LearningObjectiveCourse $course
	 * @return string
	 */
	protected function getSQL(LearningObjectiveCourse $course) {
		return 'SELECT alo_suggestion.user_id FROM alo_suggestion
				LEFT JOIN alo_notification ON 
					(alo_notification.course_obj_id = alo_suggestion.course_obj_id AND alo_notification.user_id = alo_suggestion.user_id)
				WHERE alo_suggestion.course_obj_id = ' . $this->db->quote($course->getId(), 'integer') . ' AND alo_notification.sent_at IS NULL
				GROUP BY alo_suggestion.user_id';
	}

}