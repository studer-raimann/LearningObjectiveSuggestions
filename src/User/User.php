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
	 * @param \ilObjUser $user
	 */
	public function __construct(\ilObjUser $user) {
		$this->user = $user;
	}

	/**
	 * @return int
	 */
	public function getId() {
		return $this->user->getId();
	}

	/**
	 * @return string
	 */
	public function getLogin() {
		return $this->user->getLogin();
	}

}