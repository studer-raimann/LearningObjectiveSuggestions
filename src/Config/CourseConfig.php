<?php

namespace SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Config;

use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Suggestion\LearningObjectiveSuggestion;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Score\LearningObjectiveScore;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Notification\Notification;


/**
 * Class CourseConfig
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Config
 */
class CourseConfig extends \ActiveRecord {

	const TABLE_NAME = "alo_crs_config";


	/**
	 * @return string
	 */
	public function getConnectorContainerName() {
		return self::TABLE_NAME;
	}


	/**
	 * @return string
	 * @deprecated
	 */
	public static function returnDbTableName() {
		return self::TABLE_NAME;
	}


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


	public function delete() {

		foreach(LearningObjectiveSuggestion::where(['course_obj_id' => $this->getCourseObjId()])->get() as $learning_objective_suggestions) {
			/**
			 * @var LearningObjectiveSuggestion $$learning_objective_suggestions
			 */
			$learning_objective_suggestions->delete();
		}

		foreach(LearningObjectiveScore::where(['course_obj_id' => $this->getCourseObjId()])->get() as $learning_objective_score) {
			/**
			 * @var LearningObjectiveScore $learning_objective_score
			 */
			$learning_objective_score->delete();
		}

		foreach(Notification::where(['course_obj_id' => $this->getCourseObjId()])->get() as $notification) {
			/**
			 * @var Notification $notification
			 */
			$notification->delete();
		}

		parent::delete();
	}


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
}
