<?php namespace SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\User;

/**
 * Class StudyProgram
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\User
 */
class StudyProgram {

	/**
	 * @var int
	 */
	protected $id;

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @param $id
	 * @param $title
	 */
	public function __construct($id, $title) {
		$this->id = $id;
		$this->title = $title;
	}

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

}