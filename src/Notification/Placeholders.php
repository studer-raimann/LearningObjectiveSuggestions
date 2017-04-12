<?php namespace SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Notification;

use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\LearningObjective\LearningObjective;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\LearningObjective\LearningObjectiveCourse;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\User\User;

/**
 * Class Placeholders
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Notification
 */
class Placeholders {

	/**
	 * @return array
	 */
	public function getAvailablePlaceholders() {
		return array(
			'user.getLogin' => 'Login',
			'user.getFirstname' => 'Vorname',
			'user.getLastname' => 'Nachname',
			'user.getEmail' => 'E-Mail',
			'course.getTitle' => 'Kurs-Titel',
			'course.getLink' => 'Link zum Kurs',
			'objectives' => 'Liste der empfohlenen Lernziele'
		);
	}

	/**
	 * @param LearningObjectiveCourse $course
	 * @param User $user
	 * @param array $objectives
	 * @return array
	 */
	public function getPlaceholders(LearningObjectiveCourse $course, User $user, array $objectives) {
		return array(
			'user' => $user,
			'course' => $course,
			'objectives' => $this->renderObjectives($objectives),
		);
	}

	/**
	 * @param LearningObjective[] $objectives
	 * @return string
	 */
	protected function renderObjectives(array $objectives) {
		$titles = array_map(function ($objective) {
			/** @var LearningObjective $objective */
			return ($objective) ? $objective->getTitle() : '';
		}, $objectives);
		return implode("\n", $titles);
	}

}