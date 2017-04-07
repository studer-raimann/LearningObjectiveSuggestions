<?php namespace SRAG\ILIAS\Plugins\AutoLearningObjectives\Cron;

use SRAG\ILIAS\Plugins\AutoLearningObjectives\Config\CourseConfigProvider;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\Log\Log;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\Notification\InternalMailSender;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\Notification\Notification;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\User\User;

require_once('./Services/Cron/classes/class.ilCronJob.php');
require_once('./Services/Cron/classes/class.ilCronJobResult.php');
require_once('./Services/User/classes/class.ilObjUser.php');

/**
 * Class SendSuggestionsCronJob
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\AutoLearningObjectives\Cron
 */
class SendSuggestionsCronJob extends \ilCronJob {

	/**
	 * @var \ilDB
	 */
	protected $db;

	/**
	 * @var CourseConfigProvider
	 */
	protected $config;

	/**
	 * @var Log
	 */
	protected $log;

	/**
	 * @param \ilDB $db
	 * @param CourseConfigProvider $config
	 * @param Log $log
	 */
	public function __construct(\ilDB $db, CourseConfigProvider $config, Log $log) {
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
		$set = $this->db->query($this->getSQL());
		while ($row = $this->db->fetchObject($set)) {
			$this->send($row->course_obj_id, $row->user_id);
		}
		$result = new \ilCronJobResult();
		$result->setStatus(\ilCronJobResult::STATUS_OK);
		return $result;
	}

	/**
	 * @param int $course_obj_id
	 * @param int $user_id
	 */
	protected function send($course_obj_id, $user_id) {
		// Todo Parse template and send actual suggestions
		$sender = new InternalMailSender();
		$sender->from(new User(new \ilObjUser($this->config->get('notification_sender_user_id'))))
			->to(new User(new \ilObjUser($user_id)))
			->subject($this->config->get('email_subject'))
			->body($this->config->get('email_body'));
		if ($sender->send()) {
			$notification = new Notification();
			$notification->setUserId($user_id);
			$notification->setCourseObjId($course_obj_id);
			$notification->setSentAt(date('Y-m-d H:i:s'));
			$notification->setSentUserId($this->config->get('notification_sender_user_id'));
			$notification->save();
		}
	}


	/**
	 * @return string
	 */
	protected function getSQL() {
		return 'SELECT alo_suggestion.user_id, alo_suggestion.course_obj_id FROM alo_suggestion
				LEFT JOIN alo_notification ON 
					(alo_notification.course_obj_id = alo_suggestion.course_obj_id AND alo_notification.user_id = alo_suggestion.user_id)
				WHERE alo_notification.sent_at IS NULL
				GROUP BY alo_suggestion.user_id, alo_suggestion.course_obj_id';
	}

}