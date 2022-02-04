<?php

namespace SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Suggestion;

use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\LearningObjective\LearningObjectiveCourse;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\User\User;

/**
 * Class LearningObjectiveSuggestions
 *
 * @package SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\LearningObjective
 */
class LearningObjectiveSuggestions {

	/**
	 * @var LearningObjectiveCourse
	 */
	protected $course;
	/**
	 * @var User
	 */
	protected $user;


	/**
	 * LearningObjectiveSuggestions constructor.
	 *
	 * @param LearningObjectiveCourse $course
	 * @param User         $user
	 */
	public function __construct(LearningObjectiveCourse $course, User $user) {
		$this->course = $course;
		$this->user = $user;
	}


	/**
	 * @return LearningObjectiveSuggestion[]
	 */
	public function getSuggestions() {
		return LearningObjectiveSuggestion::where(array(
			'user_id' => $this->user->getId(),
			'course_obj_id' => $this->course->getId()))->get();
	}


	/**
	 * @return bool
	 */
	public function isCronInactive() {
		return LearningObjectiveSuggestion::where(array(
			'user_id' => $this->user->getId(),
			'course_obj_id' => $this->course->getId(),
			'is_cron_active' => 0))->hasSets();
	}

	public function setCronActive() {
		foreach($this->getSuggestions() as $suggestion) {
			$suggestion->setIsCronActive(1);
			$suggestion->store();
		}
	}

	public function setCronInactive() {
		foreach($this->getSuggestions() as $suggestion) {
			$suggestion->setIsCronActive(0);
			$suggestion->store();
		}
	}

	/**
	 * Checks if cron is set to inactve for the given course/user pair
	 *
	 * @return bool
	 */
	protected function isCronInactiveForUserSuggestions() {
		return LearningObjectiveSuggestion::where(array(
			'user_id' => $this->user->getId(),
			'course_obj_id' => $this->course->getILIASCourse()->getId(),
			'is_cron_active' => 0
		))->hasSets();
	}
}
