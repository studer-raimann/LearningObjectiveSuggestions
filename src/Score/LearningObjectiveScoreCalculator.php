<?php namespace SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Score;

use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Config\CourseConfigProvider;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\LearningObjective\LearningObjectiveResult;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Log\Log;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\User\StudyProgramQuery;

/**
 * Class UserScoreCalculation
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\LearningObjective
 */
class LearningObjectiveScoreCalculator {

	/**
	 * @var CourseConfigProvider
	 */
	protected $config;
	/**
	 * @var Log
	 */
	protected $log;
	/**
	 * @var StudyProgramQuery
	 */
	protected $study_program_query;


	/**
	 * @param CourseConfigProvider $config
	 * @param StudyProgramQuery    $study_program_query
	 * @param Log                  $log
	 */
	public function __construct(CourseConfigProvider $config, StudyProgramQuery $study_program_query, Log $log) {
		$this->config = $config;
		$this->log = $log;
		$this->study_program_query = $study_program_query;
	}


	/**
	 * @param LearningObjectiveResult $objective_result
	 *
	 * @return float
	 * @throws \ilException
	 */
	public function calculate(LearningObjectiveResult $objective_result) {
		$user = $objective_result->getUser();
		$objective = $objective_result->getLearningObjective();
		//		$weight_fine = $this->config->getWeightFine($objective);
		$study_program = $this->study_program_query->getByUser($user);
		if ($study_program === NULL) {
			throw new \ilException("No study program assigned to user $user");
		}
		$weight_rough = $this->config->getWeightRough($objective, $study_program);
		if ($weight_rough === NULL) {
			$message = "Rough weight is not set for learning objective/study program pair (%s)";
			throw new \ilException(sprintf($message, $objective->getTitle() . '/' . $study_program->getTitle()));
		}
		//		if ($weight_fine === null) {
		//			throw new \ilException(sprintf('Fine weight is not set for learning objective %s', $objective->getTitle()));
		//		}
		$percentage = $objective_result->getPercentage();

		return (100 - $percentage) * (float)$weight_rough;
		//		if ($percentage < 90) {
		//			$score = (100 - $percentage) * (float) $weight_rough + (float) $weight_fine;
		//		} else {
		//			$score = (100 - $percentage) * (float) $weight_rough - (float) $weight_fine;
		//		}
		//		return max($score, 0);
	}
}
