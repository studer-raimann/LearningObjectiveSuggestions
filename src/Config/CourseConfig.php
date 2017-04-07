<?php namespace SRAG\ILIAS\Plugins\AutoLearningObjectives\Config;

/**
 * Class CourseConfig
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\AutoLearningObjectives\Config
 */
class CourseConfig extends \ActiveRecord {

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
	 */
	protected $course_obj_id;

	/**
	 * @var string
	 *
	 * @db_has_field    true
	 * @db_fieldtype    text
	 * @db_length       64
	 */
	protected $cfg_key;

	/**
	 * @var string
	 *
	 * @db_has_field    true
	 * @db_fieldtype    clob
	 */
	protected $value;


	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
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
	public function getKey() {
		return $this->cfg_key;
	}

	/**
	 * @param string $key
	 */
	public function setKey($key) {
		$this->cfg_key = $key;
	}

	/**
	 * @return string
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * @param string $value
	 */
	public function setValue($value) {
		$this->value = $value;
	}


	/**
	 * @inheritdoc
	 */
	static function returnDbTableName() {
		return 'alo_crs_config';
	}
}