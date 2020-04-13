<?php

namespace SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\User;

require_once __DIR__ . "/../../../../../User/UDFDefinition/CascadingSelect/classes/class.ilCascadingSelectSettings.php";

use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Config\CourseConfigProvider;

/**
 * Class StudyProgramQuery
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\User
 */
class StudyProgramQuery {

	/**
	 * @var CourseConfigProvider
	 */
	protected $config;
	/**
	 * @var \ilSetting
	 */
	protected $udf_setting;


	/**
	 * @param CourseConfigProvider $config
	 */
	public function __construct(CourseConfigProvider $config) {
		$this->config = $config;
		$this->udf_setting = new \ilSetting('udfd');
	}


	/**
	 * Returns all the StudyPrograms
	 *
	 * @return StudyProgram[]
	 */
	public function getAll() {
		static $cache = array();
		if (isset($cache[$this->config->getCourse()->getId()])) {
			return $cache[$this->config->getCourse()->getId()];
		}
		$programs = array();

        $settings = \ilCascadingSelectSettings::getInstance();
        $options = $settings->get('json_' . $this->config->get('udf_id_study_program'));

        $data = json_decode($options, true);
        $program_titles = array();
        // The study programs are options on the second level of all data available on the first level
        foreach ($data['options'] as $level1) {
            foreach ($level1['options'] as $level2) {
                $program_titles[] = $level2['name'];
            }
        }
        foreach (array_unique($program_titles) as $id => $title) {
            $programs[] = new StudyProgram($id, $title);
        }

		$cache[$this->config->getCourse()->getId()] = $programs;

		return $programs;
	}


	/**
	 * Returns the StudyProgram of the given User
	 *
	 * @param User $user
	 *
	 * @return StudyProgram|null
	 */
	public function getByUser(User $user) {
		$data = new \ilUserDefinedData($user->getId());
		$title = $data->get('f_' . $this->config->get('udf_id_study_program'));

        // The data is separated with an arrow, wtf...
        list($_, $title, $_) = array_map('trim', explode("→", $title));

       	$filtered = array_filter($this->getAll(), function ($study_program) use ($title) {
			/** @var $study_program StudyProgram */
			return ($study_program->getTitle() == $title);
		});

		return count($filtered) ? array_pop($filtered) : NULL;
	}

}