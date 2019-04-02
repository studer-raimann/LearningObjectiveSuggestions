<?php

namespace SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\User;

/**
 * Class User
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\User
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


	/**
	 * @return string
	 */
	public function getEmail() {
		return $this->user->getEmail();
	}


	/**
	 * @return string
	 */
	public function getFirstname() {
		return $this->user->getFirstname();
	}


	/**
	 * @return string
	 */
	public function getLastname() {
		return $this->user->getLastname();
	}


	/**
	 * @return string
	 */
	function __toString() {
		return '[' . implode(', ', array(
				$this->getId(),
				$this->getLogin(),
				$this->getEmail(),
			)) . ']';
	}
}