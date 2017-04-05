<?php namespace SRAG\ILIAS\Plugins\AutoLearningObjectives\Score;

use SRAG\ILIAS\Plugins\AutoLearningObjectives\Config\ConfigProvider;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\LearningObjective\LearningObjectiveResult;

/**
 * Class UserScoreCalculation
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\AutoLearningObjectives\LearningObjective
 */
class UserScoreCalculator {

	/**
	 * @var LearningObjectiveResult
	 */
	protected $objective_result;

	/**
	 * @var ConfigProvider
	 */
	protected $config;

	/**
	 * @param LearningObjectiveResult $objective_result
	 * @param ConfigProvider $config
	 */
	public function __construct(LearningObjectiveResult $objective_result, ConfigProvider $config) {
		$this->objective_result = $objective_result;
		$this->config = $config;
	}

	/**
	 * @return int
	 */
	public function calculate() {
		// TODO Handle errors? E.g. getStudyProgram returns null --> use constant 1 or abort calculation?
		$user = $this->objective_result->getUser();
		$objective = $this->objective_result->getLearningObjective();
		return $this->objective_result->getPercentage() *
			$this->config->getWeightRough($objective, $user->getStudyProgram()) *
			$this->config->getWeightFine($objective);
	}

}