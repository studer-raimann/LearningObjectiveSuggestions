<?php
include_once("./Services/Cron/classes/class.ilCronHookPlugin.php");
require_once(dirname(__DIR__) . '/vendor/autoload.php');

use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Config\ConfigProvider;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Cron\CalculateScoresAndSuggestionsCronJob;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Cron\SendSuggestionsCronJob;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Log\Log;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Notification\TwigParser;


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
				$log
			);
			$cron2 = new SendSuggestionsCronJob($ilDB, $config, new TwigParser(), $log);
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

	/**
	 * @inheritdoc
	 */
	protected function afterUninstall() {
		global $ilDB;
		$ilDB->dropTable(\SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Score\LearningObjectiveScore::returnDbTableName(), false);
		$ilDB->dropTable(\SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Suggestion\LearningObjectiveSuggestion::returnDbTableName(), false);
		$ilDB->dropTable(\SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Config\CourseConfig::returnDbTableName(), false);
		$ilDB->dropTable(\SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Config\Config::returnDbTableName(), false);
		$ilDB->dropTable(\SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Notification\Notification::returnDbTableName(), false);
	}

}