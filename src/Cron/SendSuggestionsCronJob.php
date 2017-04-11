<?php namespace SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Cron;

use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Config\ConfigProvider;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Config\CourseConfigProvider;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\LearningObjective\LearningObjectiveCourse;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Log\Log;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Notification\InternalMailSender;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Notification\Notification;
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
	 * @param \ilDB $db
	 * @param ConfigProvider $config
	 * @param Log $log
	 */
	public function __construct(\ilDB $db, ConfigProvider $config, Log $log) {
		$this->db = $db;
		$this->config = $config;
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
			$this->send($course, $row->user_id);
		}
	}

	/**
	 * @param LearningObjectiveCourse $course
	 * @param int $user_id
	 */
	protected function send(LearningObjectiveCourse $course, $user_id) {
		// Todo Parse template and send actual suggestions
		$config = new CourseConfigProvider($course);
		$mail = new InternalMailSender();
		$sender = $this->getSendUser($config->get('notification_sender_user_id'));
		$receiver = new User(new \ilObjUser($user_id));
		$mail->from($sender)
			->to($receiver)
			->subject($config->get('email_subject'))
			->body($config->get('email_body'));
		if ($role_id = $config->get('notification_cc_role_id')) {
			$mail->cc($this->getRole((int) $role_id));
		}
		if ($mail->send()) {
			$notification = new Notification();
			$notification->setUserId($user_id);
			$notification->setCourseObjId($course->getId());
			$notification->setSentAt(date('Y-m-d H:i:s'));
			$notification->setSentUserId($config->get('notification_sender_user_id'));
			$notification->save();
		}
	}

	/**
	 * @param int $role_id
	 * @return \ilObjRole
	 */
	protected function getRole($role_id) {
		static $cache = array();
		if (isset($cache[$role_id])) {
			return $cache[$role_id];
		}
		$role = new \ilObjRole((int) $role_id);
		$cache[$role_id] = $role;
		return $role;
	}

	/**
	 * @param int $user_id
	 * @return User
	 */
	protected function getSendUser($user_id) {
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