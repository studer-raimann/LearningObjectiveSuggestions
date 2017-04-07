<?php namespace SRAG\ILIAS\Plugins\AutoLearningObjectives\Form;

use SRAG\ILIAS\Plugins\AutoLearningObjectives\Config\CourseConfigProvider;

require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');

/**
 * Class NotificationConfigFormGUI
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\AutoLearningObjectives\Form
 */
class NotificationConfigFormGUI extends \ilPropertyFormGUI {

	/**
	 * @var CourseConfigProvider
	 */
	protected $config;

	/**
	 * @param CourseConfigProvider $config
	 */
	public function __construct(CourseConfigProvider $config) {
		parent::__construct();
		$this->config = $config;
		$this->init();
	}

	protected function init() {
		$this->setTitle('Konfiguration');

		$item = new \ilNumberInputGUI('User-ID Absender', 'notification_sender_user_id');
		$item->setInfo('User-ID eines ILIAS Benutzers, welcher die Mails versendet.');
		$item->setRequired(true);
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

		$this->addCommandButton('saveNotifications', 'Speichern');
		$this->addCommandButton('cancel', 'Abbrechen');
	}
}