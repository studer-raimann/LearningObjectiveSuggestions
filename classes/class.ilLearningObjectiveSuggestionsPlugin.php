<?php

require_once __DIR__ . "/../vendor/autoload.php";

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

	const PLUGIN_ID = "dhbwautolo";
	const PLUGIN_NAME = "LearningObjectiveSuggestions";
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
		if (static::$instance === NULL) {
			static::$instance = new self();
		}

		return static::$instance;
	}


	/**
	 * @return array
	 */
	public static function getCronInstances() {
		global $DIC;
		$ilDB = $DIC->database();
		if (static::$cron_instances === NULL) {
			$config = new ConfigProvider();
			$log = new Log();
			$cron1 = new CalculateScoresAndSuggestionsCronJob($ilDB, $config, $log);
			$cron2 = new SendSuggestionsCronJob($ilDB, $config, new TwigParser(), $log);
			static::$cron_instances = array(
				$cron1->getId() => $cron1,
				$cron2->getId() => $cron2,
			);
		}

		return static::$cron_instances;
	}


	/**
	 * @var ilDB
	 */
	protected $db;


	public function __construct() {
		parent::__construct();

		global $DIC;

		$this->db = $DIC->database();
	}


	/**
	 * @return array
	 */
	public function getCronJobInstances() {
		return self::getCronInstances();
	}


	/**
	 * @param $a_job_id
	 *
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
		return self::PLUGIN_NAME;
	}


	/**
	 * @return bool
	 */
	protected function beforeUninstall() {
		$this->db->dropTable(\SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Score\LearningObjectiveScore::TABLE_NAME, false);
		$this->db->dropTable(\SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Suggestion\LearningObjectiveSuggestion::TABLE_NAME, false);
		$this->db->dropTable(\SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Config\CourseConfig::TABLE_NAME, false);
		$this->db->dropTable(\SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Config\Config::TABLE_NAME, false);
		$this->db->dropTable(\SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Notification\Notification::TABLE_NAME, false);

		return true;
	}
}
