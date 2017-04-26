<?php namespace SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Notification;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Config\CourseConfigProvider;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\LearningObjective\LearningObjectiveCourse;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Log\Log;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\User\User;

require_once('./Services/AccessControl/classes/class.ilObjRole.php');
require_once('./Services/User/classes/class.ilObjUser.php');

/**
 * Class Sender
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Notification
 */
class Sender {
	/**
	 * @var string
	 */
	protected $subject;

	/**
	 * @var string
	 */
	protected $body;

	/**
	 * @var LearningObjectiveCourse
	 */
	protected $course;

	/**
	 * @var User
	 */
	protected $user;

	/**
	 * @var Log
	 */
	protected $log;

	/**
	 * @param LearningObjectiveCourse $course
	 * @param User $user
	 * @param Log $log
	 */
	public function __construct(LearningObjectiveCourse $course, User $user, Log $log) {
		$this->course = $course;
		$this->user = $user;
		$this->log = $log;
	}

	/**
	 * @param string $subject
	 * @return $this
	 */
	public function subject($subject) {
		$this->subject = $subject;
		return $this;
	}

	/**
	 * @param string $body
	 * @return $this
	 */
	public function body($body) {
		$this->body = $body;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function send() {
		$config = new CourseConfigProvider($this->course);
		$sender = new User(new \ilObjUser($config->get('notification_sender_user_id')));
		$mail = new InternalMail();
		$mail->subject($this->subject)
			->body($this->body)
			->from($sender)
			->to($this->user);
		if ($cc_role_id = $config->get('notification_cc_role_id')) {
			$mail->cc($this->getRole($cc_role_id));
		}
		try {
			$mail->send();
			$notification = $this->getNotification();
			$notification->setSentUserId($sender->getId());
			$notification->setSentAt(date('Y-m-d H:i:s'));
			$notification->save();
			return true;
		} catch (\Exception $e) {
			$this->log->write($e->getMessage());
			return false;
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
	 * @return Notification|\ActiveRecord
	 */
	protected function getNotification() {
		$notification = Notification::where(array(
			'user_id' => $this->user->getId(),
			'course_obj_id' => $this->course->getId()
		))->first();
		if ($notification !== null) {
			return $notification;
		}
		$notification = new Notification();
		$notification->setUserId($this->user->getId());
		$notification->setCourseObjId($this->course->getId());
		return $notification;
	}

}