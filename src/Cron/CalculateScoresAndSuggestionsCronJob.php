<?php namespace SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Cron;

use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Config\ConfigProvider;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Config\CourseConfigProvider;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\LearningObjective\LearningObjective;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\LearningObjective\LearningObjectiveCourse;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\LearningObjective\LearningObjectiveQuery;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\LearningObjective\LearningObjectiveResult;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Log\Log;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Score\LearningObjectiveScore;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Score\LearningObjectiveScoreCalculator;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Suggestion\LearningObjectiveSuggestion;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Suggestion\LearningObjectiveSuggestionGenerator;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\User\StudyProgramQuery;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\User\User;

/**
 * Class CalculateScoresAndSuggestionsCronJob
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Cron
 */
class CalculateScoresAndSuggestionsCronJob extends \ilCronJob {

	const CRON_JOB_ID = "alo_calc_user_scores";
	/**
	 * @var \ilDB
	 */
	protected $db;
	/**
	 * @var ConfigProvider
	 */
	protected $config;
	/**
	 * @var Log
	 */
	protected $log;
	/**
	 * @var \ilLearningObjectiveSuggestionsPlugin
	 */
	protected $pl;


	/**
	 * @param \ilDB          $db
	 * @param ConfigProvider $config
	 * @param Log            $log
	 */
	public function __construct(\ilDB $db, ConfigProvider $config, Log $log) {
		$this->db = $db;
		$this->config = $config;
		$this->log = $log;
		$this->pl = \ilLearningObjectiveSuggestionsPlugin::getInstance();
	}


	/**
	 * @inheritdoc
	 */
	public function getTitle() {
		return $this->pl->txt("generate_suggestions");
	}


	/**
	 * @inheritdoc
	 */
	public function getDescription() {
		return $this->pl->txt("generate_suggestions_description");
	}


	/**
	 * @inheritdoc
	 */
	public function getId() {
		return self::CRON_JOB_ID;
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
		foreach ($this->config->getCourseRefIds() as $ref_id) {
			$course = new LearningObjectiveCourse(new \ilObjCourse($ref_id));
			$this->runFor($course);
		}
		$result = new \ilCronJobResult();
		$result->setStatus(\ilCronJobResult::STATUS_OK);

		return $result;
	}


	/**
	 * @param LearningObjectiveCourse $course
	 */
	protected function runFor(LearningObjectiveCourse $course) {
		$config = new CourseConfigProvider($course);
		$study_program_query = new StudyProgramQuery($config);
		$learning_objective_query = new LearningObjectiveQuery($config);
		$set = $this->db->query($this->getSQL($course));
		$objective_results = array();
		while ($row = $this->db->fetchObject($set)) {
			$objective = $this->getLearningObjective($course, $row->objective_id);
			$user = $this->getUser($row->user_id);
			$objective_results[] = new LearningObjectiveResult($objective, $user);
		}
		$users = array(); // Stores all the users where we might need to create the suggestions
		foreach ($objective_results as $objective_result) {
			/** @var LearningObjectiveResult $objective_result */
			$calculator = new LearningObjectiveScoreCalculator($config, $study_program_query, $this->log);
			$score = $this->getLearningObjectiveScore($objective_result);
			try {
				$skore = $calculator->calculate($objective_result);
				$score->setScore($skore);
				$score->save();
				$users[] = $objective_result->getUser();
			} catch (\Exception $e) {
				$this->log->write("Exception when trying to calculate the score for {$score}");
				$this->log->write($e->getMessage());
				$this->log->write($e->getTraceAsString());
			}
		}
		foreach ($users as $user) {
			// Do not create suggestions if they already exist
			if ($this->existSuggestions($course, $user)) {
				continue;
			}
			$generator = new LearningObjectiveSuggestionGenerator($config, $learning_objective_query, $this->log);
			$scores = $this->getScores($course, $user);
			$suggested_scores = $generator->generate($scores);
			$this->createSuggestions($suggested_scores);
		}
	}


	/**
	 * @param LearningObjectiveCourse $course
	 * @param User                    $user
	 *
	 * @return LearningObjectiveScore[]
	 */
	protected function getScores(LearningObjectiveCourse $course, User $user) {
		return LearningObjectiveScore::where(array(
			'user_id' => $user->getId(),
			'course_obj_id' => $course->getId()
		))->get();
	}


	/**
	 * Checks if there already exist computed suggestions for the given course/user pair
	 *
	 * @param LearningObjectiveCourse $course
	 * @param User                    $user
	 *
	 * @return bool
	 */
	protected function existSuggestions(LearningObjectiveCourse $course, User $user) {
		return LearningObjectiveSuggestion::where(array(
			'user_id' => $user->getId(),
			'course_obj_id' => $course->getId(),
		))->hasSets();
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
			$suggestion->setSort(++ $sort);
			$suggestion->save();
		}
	}


	/**
	 * @param LearningObjectiveResult $objective_result
	 *
	 * @return LearningObjectiveScore
	 */
	protected function getLearningObjectiveScore(LearningObjectiveResult $objective_result) {
		$score = LearningObjectiveScore::where(array(
			'course_obj_id' => $objective_result->getLearningObjective()->getCourse()->getId(),
			'objective_id' => $objective_result->getLearningObjective()->getId(),
			'user_id' => $objective_result->getUser()->getId()
		))->first();
		if ($score === NULL) {
			$score = new LearningObjectiveScore();
			$score->setCourseObjId($objective_result->getLearningObjective()->getCourse()->getId());
			$score->setObjectiveId($objective_result->getLearningObjective()->getId());
			$score->setUserId($objective_result->getUser()->getId());
		}

		return $score;
	}


	/**
	 * @param int $user_id
	 *
	 * @return User
	 */
	protected function getUser($user_id) {
		static $cache = array();
		if (isset($cache[$user_id])) {
			return $cache[$user_id];
		}
		$user = new User(new \ilObjUser($user_id));
		$cache[$user_id] = $user;

		return $user;
	}


	/**
	 * @param LearningObjectiveCourse $course
	 * @param int                     $objective_id
	 *
	 * @return LearningObjective
	 */
	protected function getLearningObjective(LearningObjectiveCourse $course, $objective_id) {
		static $cache = array();
		$cache_key = $course->getId() . $objective_id;
		if (isset($cache[$cache_key])) {
			return $cache[$cache_key];
		}
		$objective = new LearningObjective(new \ilCourseObjective($course->getILIASCourse(), $objective_id));
		$cache[$cache_key] = $objective;

		return $objective;
	}


	/**
	 * @param LearningObjectiveCourse $course
	 *
	 * @return string
	 */
	protected function getSQL(LearningObjectiveCourse $course) {
		$sql = 'SELECT loc_user_results.* FROM loc_user_results
				LEFT JOIN ' . LearningObjectiveScore::TABLE_NAME . ' ON 
					(
						' . LearningObjectiveScore::TABLE_NAME . '.user_id = loc_user_results.user_id 
						AND ' . LearningObjectiveScore::TABLE_NAME . '.course_obj_id = loc_user_results.course_id 
						AND ' . LearningObjectiveScore::TABLE_NAME . '.objective_id = loc_user_results.objective_id 
					)
				INNER JOIN loc_tst_run ON
					(
						loc_tst_run.container_id = loc_user_results.course_id
						AND loc_tst_run.user_id = loc_user_results.user_id
						AND loc_tst_run.objective_id = loc_user_results.objective_id
					)
				INNER JOIN tst_tests ON tst_tests.obj_fi = loc_tst_run.test_id
				INNER JOIN tst_active ON
					(
						tst_active.test_fi = tst_tests.test_id
						AND tst_active.user_fi = loc_user_results.user_id
					)
				WHERE loc_user_results.course_id = ' . $this->db->quote($course->getId(), 'integer') . ' 
					AND loc_user_results.type = 1
					AND tst_active.submitted > 0
					AND ' . LearningObjectiveScore::TABLE_NAME . '.id IS NULL ';
		// Only include users that are still member of the course
		$member_ids = $course->getMemberIds();
		if (count($member_ids)) {
			$sql .= ' AND loc_user_results.user_id IN (' . implode(',', $member_ids) . ') ';
		}
		$sql .= 'ORDER BY loc_user_results.user_id, loc_user_results.course_id';

		return $sql;
	}
}