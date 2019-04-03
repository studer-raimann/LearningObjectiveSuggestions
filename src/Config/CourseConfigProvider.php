<?php namespace SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Config;

use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\LearningObjective\LearningObjective;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\LearningObjective\LearningObjectiveCourse;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\User\StudyProgram;

/**
 * Class CourseConfigProvider
 *
 * Provides access to config data depending on the injected course
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Config
 */
class CourseConfigProvider {

	/**
	 * @var LearningObjectiveCourse
	 */
	protected $course;

	/**
	 * @param LearningObjectiveCourse $course
	 */
	public function __construct(LearningObjectiveCourse $course) {
		$this->course = $course;
	}

	/**
	 * @return LearningObjectiveCourse
	 */
	public function getCourse() {
		return $this->course;
	}

	/**
	 * @param string $key
	 * @return string
	 */
	public function get($key) {
		/** @var CourseConfig $config */
		$config = CourseConfig::where(array(
			'cfg_key' => $key,
			'course_obj_id' => $this->course->getId()
		))->first();
		return ($config) ? $config->getValue() : null;
	}


	public function delete() {
		/** @var CourseConfig $config */
		foreach(CourseConfig::where(array(
			'course_obj_id' => $this->course->getId()
		))->get() as $course_config) {
			$course_config->delete();
		}

	}

	/**
	 * @param string $key
	 * @param string $value
	 */
	public function set($key, $value) {
		$config = CourseConfig::where(array(
			'cfg_key' => $key,
			'course_obj_id' => $this->course->getId(),
		))->first();
		if ($config === null) {
			$config = new CourseConfig();
			$config->setKey($key);
			$config->setCourseObjId($this->course->getId());
		}
		$config->setValue($value);
		$config->save();
	}

	/**
	 * @return int
	 */
	public function getMaxSuggestions() {
		return (int)$this->get('max_amount_suggestions');
	}

	/**
	 * @return int
	 */
	public function getMinSuggestions() {
		return (int)$this->get('min_amount_suggestions');
	}

	/**
	 * @param LearningObjective $learning_objective
	 * @param StudyProgram $study_program
	 * @return int
	 */
	public function getWeightRough(LearningObjective $learning_objective, StudyProgram $study_program) {
		return $this->get('weight_rough_' . $learning_objective->getId() . '_' . $study_program->getId());
	}

	/**
	 * @param LearningObjective $learning_objective
	 * @return int
	 */
	public function getWeightFine(LearningObjective $learning_objective) {
		return $this->get('weight_fine_' . $learning_objective->getId());
	}

	/**
	 * @return int
	 */
	public function getBias() {
		return (int)$this->get('bias');
	}

	/**
	 * @return int
	 */
	public function getOffset() {
		return (int)$this->get('offset');
	}

	/**
	 * @return int
	 */
	public function getSteps() {
		return (int)$this->get('steps');
	}

	/**
	 * @return string
	 */
	public function getEmailSubjectTemplate() {
		return (string)$this->get('email_subject');
	}

	/**
	 * @return string
	 */
	public function getEmailBodyTemplate() {
		return (string)$this->get('email_body');
	}
}