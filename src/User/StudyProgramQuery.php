<?php namespace SRAG\ILIAS\Plugins\AutoLearningObjectives\User;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\Config\CourseConfigProvider;

require_once('./Services/User/classes/class.ilUserDefinedFields.php');
require_once('./Services/User/classes/class.ilUserDefinedData.php');


/**
 * Class StudyProgramQuery
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\AutoLearningObjectives\User
 */
class StudyProgramQuery {

	/**
	 * @var CourseConfigProvider
	 */
	protected $config;

	/**
	 * @param CourseConfigProvider $config
	 */
	public function __construct(CourseConfigProvider $config) {
		$this->config = $config;
	}

	/**
	 * Returns all the StudyPrograms
	 *
	 * @return StudyProgram[]
	 */
	public function getAll() {
		static $cache = null;
		if ($cache !== null) {
			return $cache;
		}
		/** @var \ilUserDefinedFields $udf */
		$udf = \ilUserDefinedFields::_getInstance();
		$data = $udf->getDefinition($this->config->get('udf_id_study_program'));
		$programs = array();
		foreach ($data['field_values'] as $id => $title) {
			$programs[] = new StudyProgram($id, $title);
		}
		$cache = $programs;
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
		$filtered = array_filter($this->getAll(), function($study_program) use ($title) {
			/** @var $study_program StudyProgram */
			return ($study_program->getTitle() == $title);
		});
		return count($filtered) ? array_pop($filtered) : null;
	}

}