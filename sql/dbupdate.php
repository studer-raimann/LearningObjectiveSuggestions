<#1>
<?php

use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Config\Config;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Config\CourseConfig;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Notification\Notification;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Score\LearningObjectiveScore;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Suggestion\LearningObjectiveSuggestion;

LearningObjectiveScore::updateDB();
LearningObjectiveSuggestion::updateDB();
CourseConfig::updateDB();
Config::updateDB();
Notification::updateDB();
?>
<#2>
<?php
//
?>
<#3>
<?php
SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Suggestion\LearningObjectiveSuggestion::updateDB();
?>
<#4>
<?php
foreach(SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Suggestion\LearningObjectiveSuggestion::get() as $sug) {
	/**
	 * @@var LearningObjectiveSuggestion $sug
	 */
	$sug->setIsCronActive(1);
	$sug->save();
}
?>

<#5>
<?php
    GLobal $DIC;

    $DIC->database()->query("DELETE t
     FROM alo_score t JOIN
          (SELECT user_id, course_obj_id, objective_id, min(score) AS min_score
           FROM alo_score t
           GROUP BY user_id, course_obj_id, objective_id
          ) tt
          USING (user_id, course_obj_id, objective_id)
    WHERE score > min_score;");

    $DIC->database()->query("DELETE t
         FROM alo_suggestion t JOIN
              (SELECT user_id, course_obj_id, objective_id, min(created_at) AS min_date
               FROM alo_suggestion t
               GROUP BY user_id, course_obj_id, objective_id
              ) tt
              USING (user_id, course_obj_id, objective_id)
        WHERE created_at > min_date;");


//    $DIC->database()->query("ALTER TABLE alo_score ADD UNIQUE unique_index(user_Id, course_obj_id, objective_id)");
//    $DIC->database()->query("ALTER TABLE alo_suggestion ADD UNIQUE unique_index(user_Id, course_obj_id, objective_id)");

?>
