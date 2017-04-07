<?php namespace SRAG\ILIAS\Plugins\AutoLearningObjectives\Cron;

use SRAG\ILIAS\Plugins\AutoLearningObjectives\Config\CourseConfigProvider;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\LearningObjective\LearningObjective;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\LearningObjective\LearningObjectiveQuery;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\LearningObjective\LearningObjectiveResult;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\Log\Log;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\Score\LearningObjectiveScore;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\Score\LearningObjectiveScoreCalculator;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\Suggestion\LearningObjectiveSuggestion;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\Suggestion\LearningObjectiveSuggestionGenerator;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\User\StudyProgramQuery;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\User\User;

require_once('./Services/Cron/classes/class.ilCronJob.php');
require_once('./Services/Cron/classes/class.ilCronJobResult.php');
require_once('./Modules/Course/classes/class.ilCourseObjective.php');
require_once('./Modules/Course/classes/class.ilObjCourse.php');

/**
 * Class CalculateScoresAndSuggestionsCronJob
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\AutoLearningObjectives\Cron
 */
class CalculateScoresAndSuggestionsCronJob extends \ilCronJob {

	/**
	 * @var \ilDB
	 */
	protected $db;

	/**
	 * @var CourseConfigProvider
	 */
	protected $config;

	/**
	 * @var StudyProgramQuery
	 */
	protected $study_program_query;

	/**
	 * @var Log
	 */
	protected $log;

	/**
	 * @var LearningObjectiveQuery
	 */
	protected $learning_objective_query;

	/**
	 * @param \ilDB $db
	 * @param CourseConfigProvider $config
	 * @param StudyProgramQuery $study_program_query
	 * @param LearningObjectiveQuery $learning_objective_query
	 * @param Log $log
	 */
	public function __construct(\ilDB $db,
	                            CourseConfigProvider $config,
	                            StudyProgramQuery $study_program_query,
	                            LearningObjectiveQuery $learning_objective_query,
	                            Log $log
	) {
		$this->db = $db;
		$this->config = $config;
		$this->study_program_query = $study_program_query;
		$this->log = $log;
		$this->learning_objective_query = $learning_objective_query;
	}

	/**
	 * @inheritdoc
	 */
	public function getTitle() {
		return 'Lernziel-Empfehlungen generieren';
	}

	/**
	 * @inheritdoc
	 */
	public function getDescription() {
		return 'Berechnet die Scores aller Lernziele für Benutzer, welche den Einstiegstest neu bestanden haben. ' .
			'Zusätzlich werden die empfohlenen Lernziele definiert.';
	}


	/**
	 * @inheritdoc
	 */
	public function getId() {
		return 'alo_calc_user_scores';
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
		$stack = array();
		foreach ($objective_results as $objective_result) {
			/** @var LearningObjectiveResult $objective_result */
			$calculator = new LearningObjectiveScoreCalculator($objective_result, $this->config, $this->study_program_query, $this->log);
			$score = $this->getLearningObjectiveScore($objective_result);
			try {
				$skore = $calculator->calculate();
				$score->setScore($skore);
				$score->save();
				$stack[$objective_result->getUser()->getId()][] = $score;
			} catch (\Exception $e) {
				$this->log->write("Exception when trying to calculate the score for {$score}");
				$this->log->write($e->getMessage());
				$this->log->write($e->getTraceAsString());
			}
		}
		foreach ($stack as $user_id => $scores) {
			$generator = new LearningObjectiveSuggestionGenerator($this->config, $this->learning_objective_query, $this->log);
			$suggested_scores = $generator->generate($scores);
			$this->createSuggestions($suggested_scores);
		}
		$result = new \ilCronJobResult();
		$result->setStatus(\ilCronJobResult::STATUS_OK);
		$message = sprintf("Der Score für alle Lernziele wurde für %s Benutzer berechnet.", count($objective_results));
		$result->setMessage($message);
		return $result;
	}

	/**
	 * @param LearningObjectiveScore[] $scores
	 */
	protected function createSuggestions(array $scores) {
		foreach ($scores as $sort => $score) {
			$suggestion = new LearningObjectiveSuggestion();
			$suggestion->setCourseObjId($score->getCourseObjId());
			$suggestion->setObjectiveId($score->getObjectiveId());
			$suggestion->setUserId($score->getUserId());
			$suggestion->setSort(++$sort);
			$suggestion->save();
		}
	}


	/**
	 * @param LearningObjectiveResult $objective_result
	 * @return LearningObjectiveScore
	 */
	protected function getLearningObjectiveScore(LearningObjectiveResult $objective_result) {
		$score = LearningObjectiveScore::where(array(
			'course_obj_id' => $objective_result->getLearningObjective()->getCourse()->getId(),
			'objective_id' => $objective_result->getLearningObjective()->getId(),
			'user_id' => $objective_result->getUser()->getId()
		))->first();
		if ($score === null) {
			$score = new LearningObjectiveScore();
			$score->setCourseObjId($objective_result->getLearningObjective()->getCourse()->getId());
			$score->setObjectiveId($objective_result->getLearningObjective()->getId());
			$score->setUserId($objective_result->getUser()->getId());
		}
		return $score;
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
		$obj_id = \ilObject::_lookupObjId($this->config->get('ref_id_course'));
		return 'SELECT loc_user_results.* FROM loc_user_results
				LEFT JOIN alo_score ON 
					(
					alo_score.user_id = loc_user_results.user_id 
					AND alo_score.course_obj_id = loc_user_results.course_id 
					AND alo_score.objective_id = loc_user_results.objective_id 
					AND loc_user_results.type = 1
					)
				WHERE loc_user_results.course_id = ' . $this->db->quote($obj_id, 'integer') . ' 
				AND alo_score.id IS NULL
				ORDER BY loc_user_results.user_id, loc_user_results.course_id';
	}

}