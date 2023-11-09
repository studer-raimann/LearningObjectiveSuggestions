<?php namespace SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\LearningObjective;

use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Config\CourseConfigProvider;

/**
 * Class LearningObjectiveCourse
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\LearningObjective
 */
class LearningObjectiveCourse {
	protected \ilObjCourse $course;
	/**
	 * LearningObjectiveCourse constructor.
	 */
	public function __construct(\ilObjCourse $course) {
		$this->course = $course;
	}
	public function getILIASCourse(): \ilObjCourse
    {
		return $this->course;
	}
	public function getId(): int
    {
		return $this->course->getId();
	}
	public function getTitle(): string
    {
		return $this->course->getTitle();
	}
	public function getRefId(): int
    {
		return $this->course->getRefId();
	}
	public function getIsCronInactive(): bool
    {
		$config = new CourseConfigProvider($this);
		return $config->getIsCronInactive();
	}
	public function getLink(): string
    {
		return \ilLink::_getStaticLink($this->getRefId(), 'crs');
	}
	/**
	 * Get the user-IDs of all members of this course
	 */
	public function getMemberIds(): array
    {
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