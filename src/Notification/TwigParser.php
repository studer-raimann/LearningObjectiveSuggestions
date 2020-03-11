<?php namespace SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Notification;

/**
 * Class TwigNotificationParser
 *
 * Uses the twig template engine to parse notification templates
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Notification
 */
class TwigParser implements Parser {

	/**
	 * @inheritdoc
	 */
	public function parse($template, array $placeholders) {
		$twig = $this->getTwig();
		$tpl = $twig->createTemplate($template);

		return $tpl->render($placeholders);
	}


	/**
	 * @inheritdoc
	 */
	public function isValid($template, array $placeholders) {
		try {
			$this->parse($template, $placeholders);

			return true;
		} catch (\Exception $e) {
			return false;
		}
	}


	/**
	 * @return \Twig_Environment
	 */
	protected function getTwig() {
		static $instance = NULL;
		if ($instance !== NULL) {
			return $instance;
		}
		$loader = new \Twig_Loader_Array([]);
		$twig = new \Twig_Environment($loader, array( 'autoescape' => false ));
		$instance = $twig;

		return $twig;
	}
}