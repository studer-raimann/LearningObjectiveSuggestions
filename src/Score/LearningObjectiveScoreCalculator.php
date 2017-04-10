<?php namespace SRAG\ILIAS\Plugins\AutoLearningObjectives\Score;

use SRAG\ILIAS\Plugins\AutoLearningObjectives\Config\CourseConfigProvider;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\LearningObjective\LearningObjectiveResult;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\Log\Log;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\User\StudyProgramQuery;

/**
 * Class UserScoreCalculation
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\AutoLearningObjectives\LearningObjective
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
	 * @param StudyProgramQuery $study_program_query
	 * @param Log $log
	 */
	public function __construct(
		CourseConfigProvider $config,
		StudyProgramQuery $study_program_query,
		Log $log)
	{
		$this->config = $config;
		$this->log = $log;
		$this->study_program_query = $study_program_query;
	}

	/**
	 * @param LearningObjectiveResult $objective_result
	 * @return int
	 * @throws \ilException
	 */
	public function calculate(LearningObjectiveResult $objective_result) {
		$user = $objective_result->getUser();
		$objective = $objective_result->getLearningObjective();
		$weight_fine = $this->config->getWeightFine($objective);
		$study_program = $this->study_program_query->getByUser($user);
		if ($study_program === null) {
			throw new \ilException("No study program assigned to user $user");
		}
		$weight_rough = $this->config->getWeightRough($objective, $study_program);
		if ($weight_rough === null) {
			$message = "Rough weight is not set for learning objective/study program pair (%s)";
			throw new \ilException(sprintf($message, $objective->getTitle() . '/' . $study_program->getTitle()));
		}
		if ($weight_fine === null) {
			throw new \ilException(sprintf('Fine weight is not set for learning objective %s', $objective->getTitle()));
		}
		return (100 - $objective_result->getPercentage()) * $weight_rough * $weight_fine;
	}

}