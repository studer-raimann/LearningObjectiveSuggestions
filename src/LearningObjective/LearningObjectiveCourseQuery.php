<?php namespace SRAG\ILIAS\Plugins\AutoLearningObjectives\LearningObjective;

use SRAG\ILIAS\Plugins\AutoLearningObjectives\Config\ConfigProvider;

require_once('./Modules/Course/classes/class.ilCourseObjective.php');
require_once('./Modules/Course/classes/class.ilObjCourse.php');

/**
 * Class LearningObjectiveCourseQuery
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\AutoLearningObjectives\LearningObjective
 */
class LearningObjectiveCourseQuery {

	/**
	 * @var ConfigProvider
	 */
	protected $config;

	/**
	 * @param ConfigProvider $config
	 */
	public function __construct(ConfigProvider $config) {
		$this->config = $config;
	}


	/**
	 * @return LearningObjectiveCourse[]
	 */
	public function getAll() {
		static $cache = null;
		if ($cache !== null) {
			return $cache;
		}
		$courses = array();
		foreach ($this->config->getCourseRefIds() as $ref_id) {
			$courses[] = new LearningObjectiveCourse(new \ilObjCourse($ref_id));
		}
		$cache = $courses;
		return $courses;
	}
}