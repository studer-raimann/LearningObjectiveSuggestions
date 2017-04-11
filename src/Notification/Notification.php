<?php namespace SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Notification;

/**
 * Class Notification
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Notification
 */
class Notification extends \ActiveRecord {

	/**
	 * @var int
	 *
	 * @db_has_field    true
	 * @db_fieldtype    integer
	 * @db_length       8
	 * @db_is_primary   true
	 * @db_sequence     true
	 */
	protected $id;

	/**
	 * @var int
	 *
	 * @db_has_field    true
	 * @db_fieldtype    integer
	 * @db_length       8
	 * @db_index        true
	 */
	protected $user_id;

	/**
	 * @var int
	 *
	 * @db_has_field    true
	 * @db_fieldtype    integer
	 * @db_length       8
	 * @db_index        true
	 */
	protected $course_obj_id;

	/**
	 * @var string
	 *
	 * @db_has_field    true
	 * @db_fieldtype    timestamp
	 */
	protected $sent_at;

	/**
	 * @var int
	 *
	 * @db_has_field    true
	 * @db_fieldtype    integer
	 * @db_length       8
	 */
	protected $sent_user_id;

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return int
	 */
	public function getUserId() {
		return $this->user_id;
	}

	/**
	 * @param int $user_id
	 */
	public function setUserId($user_id) {
		$this->user_id = $user_id;
	}

	/**
	 * @return int
	 */
	public function getCourseObjId() {
		return $this->course_obj_id;
	}

	/**
	 * @param int $course_obj_id
	 */
	public function setCourseObjId($course_obj_id) {
		$this->course_obj_id = $course_obj_id;
	}

	/**
	 * @return string
	 */
	public function getSentAt() {
		return $this->sent_at;
	}

	/**
	 * @param string $sent_at
	 */
	public function setSentAt($sent_at) {
		$this->sent_at = $sent_at;
	}

	/**
	 * @return int
	 */
	public function getSentUserId() {
		return $this->sent_user_id;
	}

	/**
	 * @param int $sent_user_id
	 */
	public function setSentUserId($sent_user_id) {
		$this->sent_user_id = $sent_user_id;
	}


	/**
	 * @return string
	 * @description Return the Name of your Database Table
	 * @deprecated
	 */
	static function returnDbTableName() {
		return 'alo_notification';
	}
}