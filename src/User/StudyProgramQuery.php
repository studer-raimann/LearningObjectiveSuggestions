<?php namespace SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\User;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Config\CourseConfigProvider;

require_once('./Services/User/classes/class.ilUserDefinedFields.php');
require_once('./Services/User/classes/class.ilUserDefinedData.php');
require_once('./Services/Administration/classes/class.ilSetting.php');


/**
 * Class StudyProgramQuery
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
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
		/** @var \ilUserDefinedFields $udf */
		$udf = \ilUserDefinedFields::_getInstance();
		// Check if field is of type CascadingSelect
		if (!$this->isCascadingSelect()) {
			$data = $udf->getDefinition($this->config->get('udf_id_study_program'));
			foreach ($data['field_values'] as $id => $title) {
				$programs[] = new StudyProgram($id, $title);
			}
		} else {
			$options = $this->udf_setting->get('json_' . $this->config->get('udf_id_study_program'));
			$data = json_decode($options, true);
			foreach ($data['options'][0]['options'] as $id => $row) {
				$programs[] = new StudyProgram($id, $row['name']);
			}
		}
		$cache[$this->config->getCourse()->getId()] = $programs;
		return $programs;
	}

	/**
	 * Returns the StudyProgram of the given User
	 *
	 * @param User $user
	 * @return StudyProgram|null
	 */
	public function getByUser(User $user) {
		$data = new \ilUserDefinedData($user->getId());
		$title = $data->get('f_' . $this->config->get('udf_id_study_program'));
		if ($this->isCascadingSelect()) {
			// The data is separated with an arrow, wtf...
			list($_, $title, $_) = array_map('trim', explode("→", $title));
		}
		$filtered = array_filter($this->getAll(), function($study_program) use ($title) {
			/** @var $study_program StudyProgram */
			return ($study_program->getTitle() == $title);
		});
		return count($filtered) ? array_pop($filtered) : null;
	}

	/**
	 * Check if the the UDF field is of type cascading select
	 *
	 * @return bool
	 */
	protected function isCascadingSelect() {
		return ($this->udf_setting->get('json_' . $this->config->get('udf_id_study_program')) !== false);
	}

}