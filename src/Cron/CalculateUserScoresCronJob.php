<?php namespace SRAG\ILIAS\Plugins\AutoLearningObjectives\Cron;

use SRAG\ILIAS\Plugins\AutoLearningObjectives\Config\ConfigProvider;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\LearningObjective\LearningObjective;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\LearningObjective\LearningObjectiveResult;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\Score\UserScore;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\Score\UserScoreCalculator;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\User\StudyProgramQuery;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\User\User;

require_once('./Services/Cron/classes/class.ilCronJob.php');
require_once('./Services/Cron/classes/class.ilCronJobResult.php');
require_once('./Modules/Course/classes/class.ilCourseObjective.php');
require_once('./Modules/Course/classes/class.ilObjCourse.php');

/**
 * Class CalculateUserScoresCronJob
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\AutoLearningObjectives\Cron
 */
class CalculateUserScoresCronJob extends \ilCronJob {


	const ID = 'alo_calc_user_scores';

	/**
	 * @var \ilDB
	 */
	protected $db;

	/**
	 * @var ConfigProvider
	 */
	protected $config;

	/**
	 * @var StudyProgramQuery
	 */
	protected $study_program_query;

	/**
	 * @param \ilDB $db
	 * @param ConfigProvider $config
	 * @param StudyProgramQuery $study_program_query
	 */
	public function __construct(\ilDB $db, ConfigProvider $config, StudyProgramQuery $study_program_query) {
		$this->db = $db;
		$this->config = $config;
		$this->study_program_query = $study_program_query;
	}

	/**
	 * @inheritdoc
	 */
	public function getTitle() {
		return 'Lernziel-Empfehlungen generieren';
	}

	public function getDescription() {
		return 'Berechnet die Scores aller Lernziele für Benutzer, welche den Einstiegstest neu bestanden haben. ' .
			'Zusätzlich werden die empfohlenen Lernziele definiert.';
	}


	/**
	 * @inheritdoc
	 */
	public function getId() {
		return static::ID;
	}


	/**
	 * @inheritdoc
	 */
	public function hasAutoActivation() {
		return true;
	}


	/**
	 * @inheritdoc
	 */
	public function hasFlexibleSchedule() {
		return true;
	}


	/**
	 * @inheritdoc
	 */
	public function getDefaultScheduleType() {
		return self::SCHEDULE_TYPE_IN_MINUTES;
	}


	/**
	 * @inheritdoc
	 */
	function getDefaultScheduleValue() {
		return 15;
	}


	/**
	 * @inheritdoc
	 */
	public function run() {
		$set = $this->db->query($this->getSQL());
		$objective_results = array();
		while ($row = $this->db->fetchObject($set)) {
			$objective = $this->getLearningObjective($row->course_id, $row->objective_id);
			$user = $this->getUser($row->user_id);
			$objective_results[] = new LearningObjectiveResult($objective, $user);
		}
		foreach ($objective_results as $objective_result) {
			/** @var LearningObjectiveResult $objective_result */
			$calculator = new UserScoreCalculator($objective_result, $this->config);
			$score = $calculator->calculate();
			$user_score = $this->getUserScore($objective_result);
			$user_score->setScore($score);
			$user_score->save();
		}
		$result = new \ilCronJobResult();
		$result->setStatus(\ilCronJobResult::STATUS_OK);
		$message = sprintf("Der Score für alle Lernziele wurde für %s Benutzer berechnet.", count($objective_results));
		$result->setMessage($message);
		return $result;
	}

	/**
	 * @param LearningObjectiveResult $objective_result
	 * @return UserScore
	 */
	protected function getUserScore(LearningObjectiveResult $objective_result) {
		$user_score = UserScore::where(array(
			'course_obj_id' => $objective_result->getLearningObjective()->getCourse()->getId(),
			'objective_id' => $objective_result->getLearningObjective()->getId(),
			'user_id' => $objective_result->getUser()->getId()
		))->first();
		if ($user_score === null) {
			$user_score = new UserScore();
			$user_score->setCourseObjId($objective_result->getLearningObjective()->getCourse()->getId());
			$user_score->setObjectiveId($objective_result->getLearningObjective()->getId());
			$user_score->setUserId($objective_result->getUser()->getId());
		}
		return $user_score;
	}


	/**
	 * @param int $user_id
	 * @return User
	 */
	protected function getUser($user_id) {
		static $cache = array();
		if (isset($cache[$user_id])) {
			return $cache[$user_id];
		}
		$user = new User(new \ilObjUser($user_id), $this->study_program_query);
		$cache[$user_id] = $user;
		return $user;
	}

	/**
	 * @param int $course_obj_id
	 * @param int $objective_id
	 * @return LearningObjective
	 */
	protected function getLearningObjective($course_obj_id, $objective_id) {
		static $cache = array();
		$cache_key = $course_obj_id . $objective_id;
		if (isset($cache[$cache_key])) {
			return $cache[$cache_key];
		}
		$objective = new LearningObjective(new \ilCourseObjective(new \ilObjCourse($course_obj_id, false), $objective_id));
		$cache[$cache_key] = $objective;
		return $objective;
	}


	/**
	 * @return string
	 */
	protected function getSQL() {
		return 'SELECT loc_user_results.* FROM loc_user_results
				LEFT JOIN alo_user_score ON 
					(
					alo_user_score.user_id = loc_user_results.user_id 
					AND alo_user_score.course_obj_id = loc_user_results.course_id 
					AND alo_user_score.objective_id = loc_user_results.objective_id 
					AND loc_user_results.type = 1
					)
				WHERE alo_user_score.id IS NULL
				ORDER BY loc_user_results.user_id, loc_user_results.course_id';
	}

}