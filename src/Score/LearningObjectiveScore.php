<?php namespace SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Score;

/**
 * Class LearningObjectiveScore
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\LearningObjective
 */
class LearningObjectiveScore extends \ActiveRecord {

	const TABLE_NAME = "alo_score";
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
	 * @var int
	 *
	 * @db_has_field    true
	 * @db_fieldtype    integer
	 * @db_length       8
	 * @db_index        true
	 */
	protected $objective_id;
	/**
	 * @var float
	 *
	 * @db_has_field    true
	 * @db_fieldtype    float
	 * @db_length       8
	 */
	protected $score;
	/**
	 * @var string
	 *
	 * @db_has_field    true
	 * @db_fieldtype    timestamp
	 */
	protected $created_at;
	/**
	 * @var string
	 *
	 * @db_has_field    true
	 * @db_fieldtype    timestamp
	 */
	protected $updated_at;
	/**
	 * @var int
	 *
	 * @db_has_field    true
	 * @db_fieldtype    integer
	 * @db_length       8
	 */
	protected $created_user_id;
	/**
	 * @var int
	 *
	 * @db_has_field    true
	 * @db_fieldtype    integer
	 * @db_length       8
	 */
	protected $updated_user_id;


	public function create() {
		global $DIC;
		$ilUser = $DIC->user();
		$this->created_at = date('Y-m-d H:i:s');
		$this->created_user_id = $ilUser->getId();
		parent::create();
	}


	public function update() {
		global $DIC;
		$ilUser = $DIC->user();
		$this->updated_at = date('Y-m-d H:i:s');
		$this->updated_user_id = $ilUser->getId();
		parent::update();
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
	 * @return int
	 */
	public function getObjectiveId() {
		return $this->objective_id;
	}


	/**
	 * @param int $objective_id
	 */
	public function setObjectiveId($objective_id) {
		$this->objective_id = $objective_id;
	}


	/**
	 * @return float
	 */
	public function getScore() {
		return $this->score;
	}


	/**
	 * @param float $score
	 */
	public function setScore($score) {
		$this->score = $score;
	}


	/**
	 * @return string
	 */
	public function getCreatedAt() {
		return $this->created_at;
	}


	/**
	 * @param string $created_at
	 */
	public function setCreatedAt($created_at) {
		$this->created_at = $created_at;
	}


	/**
	 * @return mixed
	 */
	public function getUpdatedAt() {
		return $this->updated_at;
	}


	/**
	 * @param mixed $updated_at
	 */
	public function setUpdatedAt($updated_at) {
		$this->updated_at = $updated_at;
	}


	/**
	 * @return int
	 */
	public function getCreatedUserId() {
		return $this->created_user_id;
	}


	/**
	 * @return int
	 */
	public function getUpdatedUserId() {
		return $this->updated_user_id;
	}


	/**
	 * @return string
	 */
	public function __toString() {
		return implode(', ', array(
			"userId => {$this->user_id}",
			"courseObjId => {$this->course_obj_id}",
			"objectiveId => {$this->objective_id}",
			"score => {$this->score}",
		));
	}


	/**
	 * @inheritdoc
	 */
	static function returnDbTableName() {
		return self::TABLE_NAME;
	}
}
