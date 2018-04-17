<?php namespace SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Log;

/**
 * Class Log
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class Log extends \ilLog {

	public function __construct() {
		parent::__construct(ILIAS_DATA_DIR, 'learning-objective-suggestions.log');
	}

}