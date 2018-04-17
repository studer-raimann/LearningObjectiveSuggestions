<?php namespace SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\LearningObjective;

/**
 * Class LearningObjective
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\LearningObjective
 */
class LearningObjective {

	/**
	 * @var \ilCourseObjective
	 */
	protected $objective;


	/**
	 * @param \ilCourseObjective $objective
	 */
	public function __construct(\ilCourseObjective $objective) {
		$this->objective = $objective;
	}


	/**
	 * @return int
	 */
	public function getId() {
		return $this->objective->getObjectiveId();
	}


	/**
	 * @return \ilObjCourse
	 */
	public function getCourse() {
		return $this->objective->getCourse();
	}


	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->objective->getTitle();
	}


	/**
	 * @return bool
	 */
	public function isActive() {
		return $this->objective->isActive();
	}


	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->objective->getDescription();
	}


	/**
	 * @return array
	 */
	public function getRefIdsOfAssignedObjects() {
		return \ilCourseObjectiveMaterials::_getAssignedMaterials($this->getId());
	}
}