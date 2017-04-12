<?php namespace SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Log;

require_once('./Services/Logging/classes/class.ilLog.php');

/**
 * Class ModificationLog
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class ModificationLog extends \ilLog {

	public function __construct() {
		parent::__construct(ILIAS_DATA_DIR, 'learning-objective-modifications.log');
	}

}