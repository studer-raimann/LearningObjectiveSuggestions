<?php namespace SRAG\ILIAS\Plugins\AutoLearningObjectives\Suggestion;

use SRAG\ILIAS\Plugins\AutoLearningObjectives\Config\ConfigProvider;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\LearningObjective\LearningObjectiveQuery;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\Log\Log;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\Score\LearningObjectiveScore;

/**
 * Class LearningObjectiveSuggestionGenerator
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\AutoLearningObjectives\Suggestion
 */
class LearningObjectiveSuggestionGenerator {

	/**
	 * @var ConfigProvider
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
	 * @param ConfigProvider $config
	 * @param LearningObjectiveQuery $learning_objective_query
	 * @param Log $log
	 */
	public function __construct(
		ConfigProvider $config,
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
		$min = $this->config->get('min_amount_suggestions');
		$max = $this->config->get('max_amount_suggestions');
		// Start by suggesting all objectives
		$suggested = $scores;
		// Truncate to max
		while (count($suggested) > $max) {
			unset($suggested[count($suggested) - 1]);
		}
		// Filter out any objectives where the Score = 0
		$filtered = array_filter($suggested, function ($score) {
			/** @var $score LearningObjectiveScore */
			return ($score->getScore() > 0);
		});
		$n = count($filtered);
		if ($n < $min) {
			// We are below the min amount of suggestions, add back some zero score objectives
			$zeros = array_values(array_filter($suggested, function ($score) {
				/** @var $score LearningObjectiveScore */
				return ($score->getScore() == 0);
			}));
			for ($i = 0; $i < ($min - $n); $i++) {
				$filtered[] = $zeros[$i];
			}
		}
		$suggested = $filtered;
		// Check that we suggest at least one learning objective from the main and extended section

		return $suggested;
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
		return $scores;
	}

}