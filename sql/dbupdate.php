<#1>
<?php

require_once __DIR__ ."/../vendor/autoload.php";

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
