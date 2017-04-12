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
		$scores = $this->sortAscByScore($scores);
		$min = $this->config->getMinSuggestions();
		$max = $this->config->getMaxSuggestions();
		// Basic check: If for some reason we do not have enough scores to satisfy the min condition, return early
		if (count($scores) <= $min) {
			return $this->sortDescByScore($scores);
		}
		$main_objective_ids = array_map(function($objective) {
			/** @var $objective LearningObjective */
			return $objective->getId();
		}, $this->learning_objective_query->getMain());
		$extended_objective_ids = array_map(function($objective) {
			/** @var $objective LearningObjective */
			return $objective->getId();
		}, $this->learning_objective_query->getExtended());

		// We split the scores in two stacks, each holding the scores based if the objective is marked as main or extended
		$main_stack = new Stack();
		$extended_stack = new Stack();
		foreach ($scores as $score) {
			if (in_array($score->getObjectiveId(), $main_objective_ids)) {
				$main_stack->push($score);
			} else if (in_array($score->getObjectiveId(), $extended_objective_ids)) {
				$extended_stack->push($score);
			}
		}
		// Start algorithm
		$suggestions = array();
		$count_main = $main_stack->count();
		$count_extended = $extended_stack->count();
		while (
			count($suggestions) < $max &&
			!($main_stack->isEmpty() && $extended_stack->isEmpty())
		) {
			/** @var LearningObjectiveScore $main */
			/** @var LearningObjectiveScore $extended */
			$main = $main_stack->peek();
			$extended = $extended_stack->peek();
			// Handle the case if stacks are already empty
			if ($main && !$extended) {
				$suggestions[] = $main_stack->pop();
				continue;
			}
			if (!$main && $extended) {
				$suggestions[] = $extended_stack->pop();
				continue;
			}
			// Both stacks still contain scores: We pick the one with the higher score
			if ($main->getScore() > $extended->getScore()) {
				$suggestions[] = $main_stack->pop();
			} else if ($extended->getScore() > $main->getScore()) {
				$suggestions[] = $extended_stack->pop();
			} else {
				// Equal score --> pick from main
				$suggestions[] = $main_stack->pop();
			}
		}
		// Check that we have chosen at least one score from both stacks
		$chosen_main = ($main_stack->count() < $count_main);
		$chosen_extended = ($extended_stack->count() < $count_extended);
		if (!$chosen_extended) {
			$suggestions[count($suggestions) - 1] = $extended_stack->pop();
		} else if (!$chosen_main) {
			$suggestions[count($suggestions) - 1] = $main_stack->pop();
		}
		// TODO Remove zero scores?
		return $this->sortDescByScore($suggestions);
	}

	/**
	 * @param LearningObjectiveScore[] $scores
	 * @return LearningObjectiveScore[]
	 */
	protected function sortAscByScore(array $scores) {
		usort($scores, function ($a, $b) {
			/** @var $a LearningObjectiveScore */
			/** @var $b LearningObjectiveScore */
			if ($a->getScore() == $b->getScore()) {
				return 0;
			}
			return ($a->getScore() < $b->getScore()) ? -1 : 1;
		});
		return $scores;
	}

	/**
	 * @param array $scores
	 * @return LearningObjectiveScore[]
	 */
	protected function sortDescByScore(array $scores) {
		return array_reverse($this->sortAscByScore($scores));
	}

}