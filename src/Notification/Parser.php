<?php namespace SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Notification;

/**
 * Interface NotificationParser
 * @package SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Notification
 */
interface Parser {

	/**
	 * Parse the template and fill placeholders
	 *
	 * @param string $template
	 * @param array $placeholders
	 * @return string
	 */
	public function parse($template, array $placeholders);

	/**
	 * Check if the template can be parsed
	 *
	 * @param string $template
	 * @param array $placeholders
	 * @return bool
	 */
	public function isValid($template, array $placeholders);
}