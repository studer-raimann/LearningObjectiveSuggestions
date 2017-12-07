<?php
require_once 'LearningObjectiveScore.php';

class LearningObjectiveScores {

	public static function getData($usr_id) {
	global $ilDB;

	$result = $ilDB->query(self::getSQL($usr_id));
	$scores = array();

	while ($row = $ilDB->fetchAssoc($result)){
		$score = new \SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Score\LearningObjectiveScore();
		$score->setCourseObjId($row['course_obj_id']);
		$score->setUserId($row['user_id']);
		$score->setScore($row['score']);
		$score->setObjectiveId($row['objective_id']);
		$score->setTitle($row['title']);
		$scores[] = $score;
	}

	return $scores;

	}







	protected static function getSQL($usr_id) {
		global $ilDB;

		$select = "select * from alo_score
					inner join crs_objectives on alo_score.objective_id = crs_objectives.objective_id 
					where user_id =".$ilDB->quote($usr_id,"integer");

		return $select;
	}


}