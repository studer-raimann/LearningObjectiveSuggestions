<?php namespace SRAG\ILIAS\Plugins\AutoLearningObjectives\User;

/**
 * Class User
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\AutoLearningObjectives\User
 */
class User {

	/**
	 * @var \ilObjUser
	 */
	protected $user;

	/**
	 * @var StudyProgramQuery
	 */
	protected $study_program_query;

	/**
	 * @param \ilObjUser $user
	 * @param StudyProgramQuery $study_program_query
	 */
	public function __construct(\ilObjUser $user, StudyProgramQuery $study_program_query) {
		$this->user = $user;
		$this->study_program_query = $study_program_query;
	}

	/**
	 * @return int
	 */
	public function getId() {
		return $this->user->getId();
	}

	/**
	 * @return StudyProgram
	 */
	public function getStudyProgram() {
		return $this->study_program_query->getByUser($this);
	}

}