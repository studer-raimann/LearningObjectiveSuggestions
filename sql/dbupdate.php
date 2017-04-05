<#1>
<?php
	require_once('./Customizing/global/plugins/Services/Cron/CronHook/LearningObjectiveSuggestions/vendor/autoload.php');
	\SRAG\ILIAS\Plugins\AutoLearningObjectives\Score\UserScore::installDB();
	\SRAG\ILIAS\Plugins\AutoLearningObjectives\Config\Config::installDB();
?>