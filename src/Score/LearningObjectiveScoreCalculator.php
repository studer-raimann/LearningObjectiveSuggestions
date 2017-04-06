<?php namespace SRAG\ILIAS\Plugins\AutoLearningObjectives\Score;

use SRAG\ILIAS\Plugins\AutoLearningObjectives\Config\ConfigProvider;
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
	 * @var LearningObjectiveResult
	 */
	protected $objective_result;

	/**
	 * @var ConfigProvider
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
	 * @param LearningObjectiveResult $objective_result
	 * @param ConfigProvider $config
	 * @param StudyProgramQuery $study_program_query
	 * @param Log $log
	 */
	public function __construct(
		LearningObjectiveResult $objective_result,
		ConfigProvider $config,
		StudyProgramQuery $study_program_query,
		Log $log)
	{
		$this->objective_result = $objective_result;
		$this->config = $config;
		$this->log = $log;
		$this->study_program_query = $study_program_query;
	}

	/**
	 * @return int
	 */
	public function calculate() {
		// TODO Handle errors? E.g. getStudyProgram returns null --> use constant 1 or abort calculation?
		$user = $this->objective_result->getUser();
		$objective = $this->objective_result->getLearningObjective();
		return (100 - $this->objective_result->getPercentage()) *
			$this->config->getWeightRough($objective, $this->study_program_query->getByUser($user)) *
			$this->config->getWeightFine($objective);
	}

}