<#1>
<?php
	require_once('./Customizing/global/plugins/Services/Cron/CronHook/LearningObjectiveSuggestions/vendor/autoload.php');
	\SRAG\ILIAS\Plugins\AutoLearningObjectives\Score\LearningObjectiveScore::installDB();
	\SRAG\ILIAS\Plugins\AutoLearningObjectives\Suggestion\LearningObjectiveSuggestion::installDB();
	\SRAG\ILIAS\Plugins\AutoLearningObjectives\Config\Config::installDB();
	\SRAG\ILIAS\Plugins\AutoLearningObjectives\Notification\Notification::installDB();
?>