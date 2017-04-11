<?php namespace SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Form;

use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Config\CourseConfigProvider;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\LearningObjective\LearningObjectiveCourse;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Notification\Parser;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Notification\Placeholders;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\User\User;

require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');

/**
 * Class NotificationConfigFormGUI
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Form
 */
class NotificationConfigFormGUI extends \ilPropertyFormGUI {

	/**
	 * @var CourseConfigProvider
	 */
	protected $config;

	/**
	 * @var Parser
	 */
	protected $parser;

	/**
	 * @param CourseConfigProvider $config
	 * @param Parser $parser
	 */
	public function __construct(CourseConfigProvider $config, Parser $parser) {
		parent::__construct();
		$this->config = $config;
		$this->parser = $parser;
		$this->init();
	}

	/**
	 * Additionally check if the strings can be parsed
	 *
	 * @return bool
	 */
	function checkInput() {
		global $ilUser;
		$result = parent::checkInput();
		$placeholders = new Placeholders();
		$ph = $placeholders->getPlaceholders($this->config->getCourse(), new User($ilUser), array());
		if (!$this->parser->isValid($this->getInput('email_subject'), $ph)) {
			/** @var \ilFormPropertyGUI $subject */
			$subject = $this->getItemByPostVar('email_subject');
			$subject->setAlert('Syntax Fehler, bitte prüfen Sie die verwendeten Platzhalter.');
			$result = false;
		}
		if (!$this->parser->isValid($this->getInput('email_body'), $ph)) {
			/** @var \ilFormPropertyGUI $body */
			$body = $this->getItemByPostVar('email_body');
			$body->setAlert('Syntax Fehler, bitte prüfen Sie die verwendeten Platzhalter.');
			$result = false;
		}
		if (!$result) {
			global $lng;
			\ilUtil::sendFailure($lng->txt("form_input_not_valid"));
		}
		return $result;
	}


	protected function init() {
		$this->setTitle('Konfiguration');

		$item = new \ilNumberInputGUI('User-ID Absender', 'notification_sender_user_id');
		$item->setInfo('User-ID eines ILIAS Benutzers, welcher die Mails versendet.');
		$item->setRequired(true);
		$item->setValue($this->config->get($item->getPostVar()));
		$this->addItem($item);

		$item = new \ilNumberInputGUI('Rollen-ID Betreuer', 'notification_cc_role_id');
		$item->setInfo('Mitglieder dieser Rolle enthalten eine CC als Mail, wenn die Empfehlungen an einen Benutzer gesendet werden.');
		$item->setValue($this->config->get($item->getPostVar()));
		$this->addItem($item);

		$item = new \ilFormSectionHeaderGUI();
		$item->setTitle('Templates');
		$this->addItem($item);

		$item = new \ilTextInputGUI('Betreff', 'email_subject');
		$item->setRequired(true);
		$item->setValue($this->config->get($item->getPostVar()));
		$this->addItem($item);

		$item = new \ilTextAreaInputGUI('Inhalt', 'email_body');
		$item->setRequired(true);
		$item->setInfo('Blub');
		$item->setRows(10);
		$item->setValue($this->config->get($item->getPostVar()));
		$this->addItem($item);

		$info = "<br>Für Betreff und Inhalt stehen folgende Platzhalter zur Verfügung:<br><br>";
		$ph = array();
		$placeholders = new Placeholders();
		foreach ($placeholders->getAvailablePlaceholders() as $key => $value) {
			$ph[] = "&lbrace;&lbrace; {$key} &rbrace;&rbrace; : {$value}";
		}
		$info .= implode('<br>', $ph);
		$item->setInfo($info);

		$this->addCommandButton('saveNotifications', 'Speichern');
		$this->addCommandButton('cancel', 'Abbrechen');
	}
}