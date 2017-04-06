<?php namespace SRAG\ILIAS\Plugins\AutoLearningObjectives\Log;

require_once('./Services/Logging/classes/class.ilLog.php');

/**
 * Class Log
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class Log extends \ilLog {

	public function __construct() {
		parent::__construct(ILIAS_DATA_DIR, 'learning-objective-suggestions.log');
	}

}