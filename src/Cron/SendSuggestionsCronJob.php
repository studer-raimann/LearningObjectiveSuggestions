<?php namespace SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Cron;

use ilCrsInitialTestState;
use ilCrsInitialTestStates;
use ilObjectTest;
use ilObjTest;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Config\ConfigProvider;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Config\CourseConfigProvider;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\LearningObjective\LearningObjective;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\LearningObjective\LearningObjectiveCourse;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\LearningObjective\LearningObjectiveQuery;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Log\Log;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Notification\Notification;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Notification\Parser;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Notification\Placeholders;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Notification\Sender;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Suggestion\LearningObjectiveSuggestion;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\User\User;

/**
 * Class SendSuggestionsCronJob
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Cron
 */
class SendSuggestionsCronJob extends \ilCronJob {

	const CRON_JOB_ID = "alo_send_suggestions";
	/**
	 * @var \ilDBInterface
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
	 * @var Parser
	 */
	protected $parser;
	/**
	 * @var \ilLearningObjectiveSuggestionsPlugin
	 */
	protected $pl;


	/**
	 * @param \ilDBInterface $db
	 * @param ConfigProvider $config
	 * @param Parser         $parser
	 * @param Log            $log
	 */
	public function __construct($db, ConfigProvider $config, Parser $parser, Log $log) {
		$this->db = $db;
		$this->config = $config;
		$this->parser = $parser;
		$this->log = $log;
		$this->pl = \ilLearningObjectiveSuggestionsPlugin::getInstance();
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
	public function getTitle() {
		return $this->pl->txt("send_suggestions");
	}


	/**
	 * @inheritdoc
	 */
	public function getDescription() {
		return $this->pl->txt("send_suggestions_description");
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
		return 60;
	}


	/**
	 * @inheritdoc
	 */
	public function run() {

		foreach ($this->config->getCourseRefIds() as $ref_id) {
			if(!\ilObject::_exists($ref_id,true)) {
				continue;
			}
			$course = new LearningObjectiveCourse(new \ilObjCourse($ref_id));
			$this->runForCourse($course);
		}
		$result = new \ilCronJobResult();
		$result->setStatus(\ilCronJobResult::STATUS_OK);

		return $result;
	}


	/**
	 * @param LearningObjectiveCourse $course
	 */
	protected function runForCourse(LearningObjectiveCourse $course) {
		$set = $this->db->query($this->getSQL($course));
		while ($row = $this->db->fetchObject($set)) {

		    $this->assignToRole($course, $row->user_id);
		    if($row->sent_at === null) {
                $this->send($course, $this->getUser($row->user_id));
            }
		}
	}

    /**
     * @param LearningObjectiveCourse $course
     * @param User                    $user
     */
	protected function assignToRole(LearningObjectiveCourse $course, int $user_id) {
        global $DIC;

        try {
            $test_result = self::getTestUserResult($user_id);
            if($test_result > 0) {
                $config = new CourseConfigProvider($course);
                $assign_role_config = json_decode($config->getRoleAssignmentConfig(),true);
                if(count($assign_role_config) > 0) {
                    foreach($assign_role_config as $config) {
                        if($config['min_points'] <= $test_result &&  $test_result <= $config['max_points']) {
                            $DIC->rbac()->admin()->assignUser($config['role'],$user_id);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->log->write("Error while trying to assign roles for learning objective suggestions: " . $e->getMessage());
            $this->log->write($e->getTraceAsString());
        }
    }


    public static function getTestUserResult(int $user_id ):int
    {
        $arr_initial_test_states = ilCrsInitialTestStates::getData([$user_id]);
        if (count($arr_initial_test_states) > 0) {
            /**
             * @var ilCrsInitialTestState
             */
            $crs_inital_test_state = $arr_initial_test_states[$user_id];

            $test_obj = new ilObjTest($crs_inital_test_state->getCrsitestItestObjId(), false);
            $participants = array($user_id);
            $data = $test_obj->getAllTestResults($participants, false);
            foreach ($data as $row) {
                $max = $row["max_points"];
                $res = $row["reached_points"];
            }
            if ($max == 0) {
                return (-1);
            }
            return ($res * 100 / $max);
        }
        return (-1);
    }


	/**
	 * @param LearningObjectiveCourse $course
	 * @param User                    $user
	 */
	protected function send(LearningObjectiveCourse $course, User $user) {
		$config = new CourseConfigProvider($course);
		$query = new LearningObjectiveQuery($config);
		$placeholders = new Placeholders();
		// Note: If we can't parse the mail templates, we fail silently but write to log
		try {
			$objectives = $this->getSuggestedLearningObjectives($course, $user, $query);
			$p = $placeholders->getPlaceholders($course, $user, $objectives);
			$subject = $this->parser->parse($config->getEmailSubjectTemplate(), $p);
			$body = $this->parser->parse($config->getEmailBodyTemplate(), $p);
			$sender = new Sender($course, $user, $this->log);
			$sender->subject($subject)->body($body);
			if (!$sender->send()) {
				$msg = "Failed to send learning objective suggestions for course %s and User %s";
				$this->log->write(sprintf($msg, $course->getTitle(), $user->__toString()));
			}
		} catch (\Exception $e) {
			$this->log->write("Error while trying to send learning objective suggestions: " . $e->getMessage());
			$this->log->write($e->getTraceAsString());
		}
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
	 * @param User                    $user
	 * @param LearningObjectiveQuery  $query
	 *
	 * @return LearningObjective[]
	 */
	protected function getSuggestedLearningObjectives(LearningObjectiveCourse $course, User $user, LearningObjectiveQuery $query) {
		$suggestions = LearningObjectiveSuggestion::where(array(
			'user_id' => $user->getId(),
			'course_obj_id' => $course->getId(),
		))->orderBy('sort')->get();
		$objectives = array();
		foreach ($suggestions as $suggestion) {
			/** @var $suggestion LearningObjectiveSuggestion */
			$objectives[] = $query->getByObjectiveId($suggestion->getObjectiveId());
		}

		return $objectives;
	}


	/**
	 * @param LearningObjectiveCourse $course
	 *
	 * @return string
	 */
	protected function getSQL(LearningObjectiveCourse $course) {
		$sql = 'SELECT 
				' . LearningObjectiveSuggestion::TABLE_NAME . '.user_id,
				' . Notification::TABLE_NAME . '.sent_at
				FROM ' . LearningObjectiveSuggestion::TABLE_NAME . '
				LEFT JOIN ' . Notification::TABLE_NAME . ' ON 
					(' . Notification::TABLE_NAME . '.course_obj_id = ' . LearningObjectiveSuggestion::TABLE_NAME . '.course_obj_id AND '
			. Notification::TABLE_NAME . '.user_id = ' . LearningObjectiveSuggestion::TABLE_NAME . '.user_id)
				WHERE 
					' . LearningObjectiveSuggestion::TABLE_NAME . '.course_obj_id = ' . $this->db->quote($course->getId(), 'integer') . ' 
					AND ' . LearningObjectiveSuggestion::TABLE_NAME .'.is_cron_active = 1';
		$member_ids = $course->getMemberIds();
		if (count($member_ids)) {
			$sql .= ' AND ' . LearningObjectiveSuggestion::TABLE_NAME . '.user_id IN (' . implode(',', $member_ids) . ') ';
		}
		$sql .= 'GROUP BY ' . LearningObjectiveSuggestion::TABLE_NAME . '.user_id';

		return $sql;
	}
}