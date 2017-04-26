<?php namespace SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Suggestion;

use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Config\CourseConfigProvider;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\LearningObjective\LearningObjective;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\LearningObjective\LearningObjectiveQuery;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Log\Log;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Score\LearningObjectiveScore;

/**
 * Class LearningObjectiveSuggestionGenerator
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Suggestion
 */
class LearningObjectiveSuggestionGenerator {

	/**
	 * @var CourseConfigProvider
	 */
	protected $config;

	/**
	 * @var LearningObjectiveQuery
	 */
	protected $learning_objective_query;

	/**
	 * @var Log
	 */
	protected $log;

	/**
	 * @param CourseConfigProvider $config
	 * @param LearningObjectiveQuery $learning_objective_query
	 * @param Log $log
	 */
	public function __construct(
		CourseConfigProvider $config,
		LearningObjectiveQuery $learning_objective_query,
		Log $log
	) {
		$this->config = $config;
		$this->learning_objective_query = $learning_objective_query;
		$this->log = $log;
	}

	/**
	 * Determines the learning objectives being suggested among the given objectives
	 *
	 * @param LearningObjectiveScore[] $scores
	 * @return LearningObjectiveScore[]
	 */
	public function generate(array $scores) {
		$scores = $this->sortDescByScore($scores);
		$min = $this->config->getMinSuggestions();
		$max = $this->config->getMaxSuggestions();
		$bias = $this->config->getBias();
		$offset = $this->config->getOffset();
		$steps = $this->config->getSteps();

		// Basic check: If for some reason we do not have enough scores to satisfy the min condition, return early
		if (count($scores) <= $min) {
			return $scores;
		}

		// For each learning objective we calculate a target score which determines if a learning objective is suggested
		$target_scores = array();
		foreach ($scores as $i => $score) {
			$pos = $i + 1;
			$target_scores[] = ($pos == 1) ? $bias : $bias + $offset + ($pos - 2) * $steps;
		}

		// Start algorithm
		// ------------------------------------------------------------------------------------------------------------

		$suggestions = array();
		foreach ($scores as $i => $score) {
			// A suggestion is made if the reached score is equal or above the target score
			if ($score->getScore() >= $target_scores[$i]) {
				$suggestions[] = $score;
			}
		}

		// Check for min condition
		if (count($suggestions) < $min) {
			$diff = $min - count($suggestions);
			// Pick the highest scores which is not currently being suggested and append them
			$candidates = array_filter($scores, function ($score) use ($suggestions) {
				return (!in_array($score, $suggestions));
			});
			for ($i = 0; $i < $diff; $i++) {
				$score = array_pop($candidates);
				if ($score) {
					$suggestions[] = $score;
				}
			}
		}

		// Check for max condition
		$suggestions = $this->sortDescByScore($suggestions);
		if (count($suggestions) > $max) {
			$offset = $max - count($suggestions); // Negative offset!
			$suggestions = array_values(array_slice($suggestions, $offset));
		}

		// Check that we have suggested at least one objective from the main and extended section
		$main_objective_ids = $this->getMainObjectiveIds();
		$extended_objective_ids = $this->getExtendedObjectiveIds();
		$main_suggestions = array_filter($suggestions, function ($suggestion) use ($main_objective_ids) {
			/** @var $suggestion LearningObjectiveScore */
			return (in_array($suggestion->getObjectiveId(), $main_objective_ids));
		});
		$extended_suggestions = array_filter($suggestions, function ($suggestion) use ($extended_objective_ids) {
			/** @var $suggestion LearningObjectiveScore */
			return (in_array($suggestion->getObjectiveId(), $extended_objective_ids));
		});
		if (count($main_suggestions) == 0 || count($extended_suggestions) == 0) {
			// Replace suggestion with lowest score either with one from the main or extended section
			$objective_ids = (count($main_suggestions) == 0) ? $main_objective_ids : $extended_objective_ids;
			$candidates = array_values(array_filter($scores, function($score) use ($objective_ids) {
				/** @var $score LearningObjectiveScore */
				return (in_array($score->getObjectiveId(), $objective_ids));
			}));
			if (count($candidates)) {
				$suggestions[count($suggestions) - 1] = $candidates[0];
			}
		}

		return $this->sortDescByScore($suggestions);
	}

	/**
	 * @param LearningObjectiveScore[] $scores
	 * @return LearningObjectiveScore[]
	 */
	protected function sortDescByScore(array $scores) {
		usort($scores, function ($a, $b) {
			/** @var $a LearningObjectiveScore */
			/** @var $b LearningObjectiveScore */
			if ($a->getScore() == $b->getScore()) {
				return 0;
			}
			return ($a->getScore() > $b->getScore()) ? -1 : 1;
		});
		return array_values($scores);
	}

//	/**
//	 * @param array $scores
//	 * @return LearningObjectiveScore[]
//	 */
//	protected function sortAscByScore(array $scores) {
//		return array_reverse($this->sortDescByScore($scores));
//	}

	/**
	 * @return array
	 */
	protected function getMainObjectiveIds() {
		return array_map(function ($objective) {
			/** @var $objective LearningObjective */
			return $objective->getId();
		}, $this->learning_objective_query->getMain());
	}

	/**
	 * @return array
	 */
	protected function getExtendedObjectiveIds() {
		return array_map(function ($objective) {
			/** @var $objective LearningObjective */
			return $objective->getId();
		}, $this->learning_objective_query->getExtended());
	}

}