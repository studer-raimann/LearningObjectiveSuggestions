<?php namespace SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\LearningObjective;

use ilObject;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Config\ConfigProvider;

/**
 * Class LearningObjectiveCourseQuery
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\LearningObjective
 */
class LearningObjectiveCourseQuery {
    protected ConfigProvider $config;
	public function __construct(ConfigProvider $config) {
		$this->config = $config;
	}
    public function getAll(): ?array
    {
		static $cache = NULL;
		if ($cache !== NULL) {
			return $cache;
		}
		$courses = array();
		foreach ($this->config->getCourseRefIds() as $ref_id) {
		    if(ilObject::_exists($ref_id, true)) {
                $courses[] = new LearningObjectiveCourse(new \ilObjCourse($ref_id));
            }

		}
		$cache = $courses;

		return $courses;
	}
}