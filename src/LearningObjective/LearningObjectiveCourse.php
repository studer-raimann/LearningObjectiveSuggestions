<?php namespace SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\LearningObjective;

/**
 * Class LearningObjectiveCourse
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\LearningObjective
 */
class LearningObjectiveCourse {

	/**
	 * @var \ilObjCourse
	 */
	protected $course;


	/**
	 * LearningObjectiveCourse constructor.
	 *
	 * @param \ilObjCourse $course
	 */
	public function __construct(\ilObjCourse $course) {
		$this->course = $course;
	}


	/**
	 * @return \ilObjCourse
	 */
	public function getILIASCourse() {
		return $this->course;
	}


	/**
	 * @return int
	 */
	public function getId() {
		return $this->course->getId();
	}


	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->course->getTitle();
	}


	/**
	 * @return int
	 */
	public function getRefId() {
		return $this->course->getRefId();
	}


	/**
	 * @return string
	 */
	public function getLink() {
		return \ilLink::_getStaticLink($this->getRefId(), 'crs');
	}


	/**
	 * Get the user-IDs of all members of this course
	 *
	 * @return array
	 */
	public function getMemberIds() {
		$participants = \ilCourseParticipants::getInstanceByObjId($this->getId());

		return $participants->getMembers();
	}


	function __toString() {
		return '[' . implode(', ', array(
				$this->getRefId(),
				$this->getTitle()
			)) . ']';
	}
}