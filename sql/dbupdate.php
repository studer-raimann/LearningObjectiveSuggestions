<#1>
<?php

require_once "Customizing/global/plugins/Services/Cron/CronHook/LearningObjectiveSuggestions/vendor/autoload.php";

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