<?php
include_once("./Services/Cron/classes/class.ilCronHookPlugin.php");
require_once(dirname(__DIR__) . '/vendor/autoload.php');

use SRAG\ILIAS\Plugins\AutoLearningObjectives\Config\ConfigProvider;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\Cron\CalculateScoresAndSuggestionsCronJob;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\Cron\SendSuggestionsCronJob;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\LearningObjective\LearningObjectiveQuery;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\User\StudyProgramQuery;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\Log\Log;


/**
 * Class ilLearningObjectiveSuggestionsPlugin
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class ilLearningObjectiveSuggestionsPlugin extends ilCronHookPlugin {

	/**
	 * @var ilLearningObjectiveSuggestionsPlugin
	 */
	protected static $instance;

	/**
	 * @var array
	 */
	protected static $cron_instances;


	/**
	 * @return ilLearningObjectiveSuggestionsPlugin
	 */
	public static function getInstance() {
		if (static::$instance === null) {
			static::$instance = new self();
		}
		return static::$instance;
	}


	/**
	 * @return array
	 */
	public static function getCronInstances() {
		if (static::$cron_instances === null) {
			global $ilDB;
			$config = new ConfigProvider();
			$log = new Log();
			$cron1 = new CalculateScoresAndSuggestionsCronJob(
				$ilDB,
				$config,
				new StudyProgramQuery($config),
				new LearningObjectiveQuery($config),
				$log
			);
			$cron2 = new SendSuggestionsCronJob($ilDB, $config, $log);
			static::$cron_instances = array(
				$cron1->getId() => $cron1,
				$cron2->getId() => $cron2,
			);
		}
		return static::$cron_instances;
	}


	/**
	 * @return array
	 */
	public function getCronJobInstances() {
		return self::getCronInstances();
	}


	/**
	 * @param $a_job_id
	 * @return ilCronJob|false
	 */
	public function getCronJobInstance($a_job_id) {
		foreach (static::getCronInstances() as $id => $cron) {
			if ($a_job_id == $id) {
				return $cron;
			}
		}
		return false;
	}


	/**
	 * @return string
	 */
	public function getPluginName() {
		return 'LearningObjectiveSuggestions';
	}
}