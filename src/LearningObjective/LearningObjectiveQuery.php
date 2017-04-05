<?php namespace SRAG\ILIAS\Plugins\AutoLearningObjectives\LearningObjective;

require_once('./Modules/Course/classes/class.ilCourseObjective.php');

/**
 * Class LearningObjectiveList
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\AutoLearningObjectives\LearningObjective
 */
class LearningObjectiveQuery {


	/**
	 * Get all learning objectives of a given course
	 *
	 * @param \ilObjCourse $course
	 * @return LearningObjective[]
	 */
	public function getByCourse(\ilObjCourse $course) {
		static $cache = array();
		if (isset($cache[$course->getId()])) {
			return $cache[$course->getId()];
		}
		$objectives = array();
		$ids = \ilCourseObjective::_getObjectiveIds($course->getId());
		foreach ($ids as $id) {
			$objectives[] = new LearningObjective(new \ilCourseObjective($course, $id));
		}
		$cache[$course->getId()] = $objectives;
		return $objectives;
	}

}