<?php namespace SRAG\ILIAS\Plugins\AutoLearningObjectives\Config;

use SRAG\ILIAS\Plugins\AutoLearningObjectives\LearningObjective\LearningObjective;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\User\StudyProgram;

/**
 * Class ConfigProvider
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\AutoLearningObjectives\Config
 */
class ConfigProvider {

	/**
	 * @param string $key
	 * @return string
	 */
	public function get($key) {
		/** @var Config $config */
		$config = Config::where(array('cfg_key' => $key))->first();
		return ($config) ? $config->getValue() : null;
	}

	/**
	 * @param string $key
	 * @param string $value
	 */
	public function set($key, $value) {
		$config = Config::where(array('cfg_key' => $key))->first();
		if ($config === null) {
			$config = new Config();
			$config->setKey($key);
		}
		$config->setValue($value);
		$config->save();
	}

	/**
	 * @param LearningObjective $learning_objective
	 * @param StudyProgram $study_program
	 * @return int
	 */
	public function getWeightRough(LearningObjective $learning_objective, StudyProgram $study_program) {
		return $this->get('weight_rough_' . $learning_objective->getId() . '_' . $study_program->getId());
	}

	/**
	 * @param LearningObjective $learning_objective
	 * @return int
	 */
	public function getWeightFine(LearningObjective $learning_objective) {
		return $this->get('weight_fine_' . $learning_objective->getId());
	}

}