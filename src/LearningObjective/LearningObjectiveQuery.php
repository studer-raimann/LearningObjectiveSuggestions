<?php namespace SRAG\ILIAS\Plugins\AutoLearningObjectives\LearningObjective;

use SRAG\ILIAS\Plugins\AutoLearningObjectives\Config\ConfigProvider;

require_once('./Modules/Course/classes/class.ilCourseObjective.php');
require_once('./Modules/Course/classes/class.ilObjCourse.php');

/**
 * Class LearningObjectiveList
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\AutoLearningObjectives\LearningObjective
 */
class LearningObjectiveQuery {

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
	 * Get all learning objectives
	 *
	 * @return LearningObjective[]
	 */
	public function getAll() {
		static $cache = array();
		$ref_id = $this->config->get('ref_id_course');
		if (isset($cache[$ref_id])) {
			return $cache[$ref_id];
		}
		$objectives = array();
		$course = new \ilObjCourse($ref_id);
		$ids = \ilCourseObjective::_getObjectiveIds($course->getId());
		foreach ($ids as $id) {
			$objectives[] = new LearningObjective(new \ilCourseObjective($course, $id));
		}
		$cache[$ref_id] = $objectives;
		return $objectives;
	}

	/**
	 * Get the learning objectives belonging to the main section
	 */
	public function getMain() {
		$main = json_decode($this->config->get('learning_objectives_main'), true);
		return array_filter($this->getAll(), function($objective) use ($main) {
			/** @var $objective LearningObjective */
			return (in_array($objective->getId(), $main));
		});
	}

	/**
	 * Get the learning objectives belonging to the extended section
	 */
	public function getExtended() {
		$extended = json_decode($this->config->get('learning_objectives_extended'), true);
		return array_filter($this->getAll(), function($objective) use ($extended) {
			/** @var $objective LearningObjective */
			return (in_array($objective->getId(), $extended));
		});
	}

}