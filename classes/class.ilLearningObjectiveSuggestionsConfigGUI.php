<?php

use SRAG\ILIAS\Plugins\AutoLearningObjectives\Config\ConfigProvider;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\Form\ConfigFormGUI;
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

	public function __construct() {
		global $tpl, $ilCtrl;
		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
	}

	function performCommand($cmd) {
		$this->config = new ConfigProvider();
		$this->$cmd();
	}

	protected function configure() {
		$form = new ConfigFormGUI($this->config, new LearningObjectiveQuery(), new StudyProgramQuery($this->config));
		$form->setFormAction($this->ctrl->getFormAction($this));
		$this->tpl->setContent($form->getHTML());
	}

	protected function cancel() {
		$this->configure();
	}

	protected function save() {
		$form = new ConfigFormGUI($this->config, new LearningObjectiveQuery(), new StudyProgramQuery($this->config));
		if ($form->checkInput()) {
			foreach ($form->getItems() as $item) {
				/** @var ilFormPropertyGUI $item */
				$value = $form->getInput($item->getPostVar());
				if ($value === null) continue;
				$value = (is_array($value)) ? json_encode($value) : $value;
				$this->config->set($item->getPostVar(), $value);
			}
			ilUtil::sendSuccess('Konfiguration gespeichert', true);
			$this->ctrl->redirect($this, 'configure');
		}
		$form->setValuesByPost();
		$this->tpl->setContent($form->getHTML());
	}
}