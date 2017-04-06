<?php

use SRAG\ILIAS\Plugins\AutoLearningObjectives\Config\ConfigProvider;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\Form\ConfigFormGUI;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\Form\NotificationConfigFormGUI;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\LearningObjective\LearningObjectiveQuery;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\User\StudyProgramQuery;

require_once('./Services/Component/classes/class.ilPluginConfigGUI.php');

/**
 * Class ilLearningObjectiveSuggestionsConfigGUI
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class ilLearningObjectiveSuggestionsConfigGUI extends ilPluginConfigGUI {

	/**
	 * @var ilTemplate
	 */
	protected $tpl;

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ConfigProvider
	 */
	protected $config;

	/**
	 * @var ilTabsGUI
	 */
	protected $tabs;

	public function __construct() {
		global $tpl, $ilCtrl, $ilTabs;
		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->tabs = $ilTabs;
	}

	function performCommand($cmd) {
		$this->config = new ConfigProvider();
		$this->$cmd();
	}

	protected function configure() {
		$this->addTabs('configure');
		$form = new ConfigFormGUI($this->config, new LearningObjectiveQuery($this->config), new StudyProgramQuery($this->config));
		$form->setFormAction($this->ctrl->getFormAction($this));
		$this->tpl->setContent($form->getHTML());
	}

	protected function notifications() {
		$this->addTabs('notifications');
		$form = new NotificationConfigFormGUI($this->config);
		$form->setFormAction($this->ctrl->getFormAction($this));
		$this->tpl->setContent($form->getHTML());
	}

	protected function cancel() {
		$this->configure();
	}

	/**
	 * @param \ilPropertyFormGUI $form
	 */
	protected function storeConfig(\ilPropertyFormGUI $form) {
		foreach ($form->getItems() as $item) {
			/** @var ilFormPropertyGUI $item */
			$value = $form->getInput($item->getPostVar());
			if ($value === null) continue;
			$value = (is_array($value)) ? json_encode($value) : $value;
			$this->config->set($item->getPostVar(), $value);
		}
	}

	protected function saveNotification() {
		$this->addTabs('notifications');
		$form = new NotificationConfigFormGUI($this->config);
		if ($form->checkInput()) {
			$this->storeConfig($form);
			ilUtil::sendSuccess('Konfiguration gespeichert', true);
			$this->ctrl->redirect($this, 'notifications');
		}
		$form->setValuesByPost();
		$this->tpl->setContent($form->getHTML());
	}

	protected function save() {
		$this->addTabs('configure');
		$form = new ConfigFormGUI($this->config, new LearningObjectiveQuery($this->config), new StudyProgramQuery($this->config));
		if ($form->checkInput()) {
			$this->storeConfig($form);
			ilUtil::sendSuccess('Konfiguration gespeichert', true);
			$this->ctrl->redirect($this, 'configure');
		}
		$form->setValuesByPost();
		$this->tpl->setContent($form->getHTML());
	}

	protected function addTabs($active = '') {
		$this->tabs->addTab('configure', 'Basis-Konfiguration', $this->ctrl->getLinkTarget($this, 'configure'));
		$this->tabs->addTab('notifications', 'Benachrichtigungen', $this->ctrl->getLinkTarget($this, 'notifications'));
		if ($active) {
			$this->tabs->setTabActive($active);
		}
	}

}