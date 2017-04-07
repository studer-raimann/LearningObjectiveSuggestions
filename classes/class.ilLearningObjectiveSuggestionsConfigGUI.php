<?php

use SRAG\ILIAS\Plugins\AutoLearningObjectives\Config\ConfigProvider;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\Config\CourseConfigProvider;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\Form\CourseConfigFormGUI;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\Form\NotificationConfigFormGUI;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\LearningObjective\LearningObjectiveCourse;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\LearningObjective\LearningObjectiveCourseQuery;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\Config\LearningObjectiveCourseTableGUI;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\LearningObjective\LearningObjectiveQuery;
use SRAG\ILIAS\Plugins\AutoLearningObjectives\User\StudyProgramQuery;

require_once('./Services/Component/classes/class.ilPluginConfigGUI.php');
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('./Modules/Course/classes/class.ilObjCourse.php');

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
	 * @var ilTabsGUI
	 */
	protected $tabs;

	/**
	 * @var ilToolbarGUI
	 */
	protected $toolbar;

	public function __construct() {
		global $tpl, $ilCtrl, $ilTabs, $ilToolbar;
		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->tabs = $ilTabs;
		$this->toolbar = $ilToolbar;
	}

	function performCommand($cmd) {
		$this->ctrl->saveParameter($this, 'course_ref_id');
		$this->$cmd();
	}

	protected function configure() {
		$button = ilLinkButton::getInstance();
		$button->setCaption('Kurs hinzufügen', false);
		$button->setUrl($this->ctrl->getLinkTarget($this, 'addCourse'));
		$this->toolbar->addButtonInstance($button);
		$table = new LearningObjectiveCourseTableGUI($this);
		$query = new LearningObjectiveCourseQuery(new ConfigProvider());
		$table->setCourses($query->getAll());
		$this->tpl->setContent($table->getHTML());
	}

	protected function configureCourse() {
		$this->addTabs('configureCourse');
		$course = new LearningObjectiveCourse(new ilObjCourse((int)$_GET['course_ref_id']));
		$this->tabs->setBackTarget('Zurück', $this->ctrl->getLinkTarget($this, 'configure'));
		$this->tpl->setTitle($course->getTitle());
		$this->tpl->setTitleIcon('');
		$config = new CourseConfigProvider($course);
		$form = new CourseConfigFormGUI($config, new LearningObjectiveQuery($config), new StudyProgramQuery($config));
		$form->setFormAction($this->ctrl->getFormAction($this));
		$this->tpl->setContent($form->getHTML());
	}

	protected function configureNotifications() {
		$this->addTabs('configureNotifications');
		$course = new LearningObjectiveCourse(new ilObjCourse((int)$_GET['course_ref_id']));
		$this->tabs->setBackTarget('Zurück', $this->ctrl->getLinkTarget($this, 'configure'));
		$this->tpl->setTitle($course->getTitle());
		$this->tpl->setTitleIcon('');
		$form = new NotificationConfigFormGUI(new CourseConfigProvider($course));
		$form->setFormAction($this->ctrl->getFormAction($this));
		$this->tpl->setContent($form->getHTML());
	}

	protected function addCourse() {
		$form = $this->getAddCourseFormGUI();
		$this->tpl->setContent($form->getHTML());
	}

	protected function saveCourse() {
		$form = $this->getAddCourseFormGUI();
		if ($form->checkInput()) {
			$config = new ConfigProvider();
			$ref_ids = (array) json_decode($config->get('course_ref_ids'), true);
			$ref_ids[] = $form->getInput('ref_id');
			$config->set('course_ref_ids', json_encode(array_unique($ref_ids)));
			ilUtil::sendSuccess('Lernzielorientierter Kurs wurde hinzugefügt und kann nun konfiguriert werden');
			$this->ctrl->redirect($this, 'configure');
		}
		$form->setValuesByPost();
		$this->tpl->setContent($form->getHTML());
	}

	/**
	 * @return ilPropertyFormGUI
	 */
	protected function getAddCourseFormGUI() {
		$form = new ilPropertyFormGUI();
		$form->setTitle('Lernzielorientierter Kurs erfassen');
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->addCommandButton('saveCourse', 'Speichern');
		$form->addCommandButton('cancel', 'Abbrechen');
		$item = new ilNumberInputGUI('Ref-ID', 'ref_id');
		$item->setInfo('Ref-ID eines lernzielorientierten Kurses');
		$item->setRequired(true);
		$form->addItem($item);
		return $form;
	}


	protected function cancel() {
		$this->configure();
	}

	/**
	 * @param CourseConfigProvider $config
	 * @param \ilPropertyFormGUI $form
	 */
	protected function storeConfig($config, \ilPropertyFormGUI $form) {
		foreach ($form->getItems() as $item) {
			/** @var ilFormPropertyGUI $item */
			$value = $form->getInput($item->getPostVar());
			if ($value === null) continue;
			$value = (is_array($value)) ? json_encode($value) : $value;
			$config->set($item->getPostVar(), $value);
		}
	}

	protected function saveNotifications() {
		$this->addTabs('configureNotifications');
		$course = new LearningObjectiveCourse(new ilObjCourse((int)$_GET['course_ref_id']));
		$config = new CourseConfigProvider($course);
		$this->tabs->setBackTarget('Zurück', $this->ctrl->getLinkTarget($this, 'configure'));
		$this->tpl->setTitle($course->getTitle());
		$this->tpl->setTitleIcon('');
		$form = new NotificationConfigFormGUI($config);
		if ($form->checkInput()) {
			$this->storeConfig($config, $form);
			ilUtil::sendSuccess('Konfiguration gespeichert', true);
			$this->ctrl->redirect($this, 'configureNotifications');
		}
		$form->setValuesByPost();
		$this->tpl->setContent($form->getHTML());
	}

	protected function save() {
		$this->addTabs('configureCourse');
		$course = new LearningObjectiveCourse(new ilObjCourse((int)$_GET['course_ref_id']));
		$this->tabs->setBackTarget('Zurück', $this->ctrl->getLinkTarget($this, 'configure'));
		$this->tpl->setTitle($course->getTitle());
		$this->tpl->setTitleIcon('');
		$config = new CourseConfigProvider($course);
		$form = new CourseConfigFormGUI($config, new LearningObjectiveQuery($config), new StudyProgramQuery($config));
		if ($form->checkInput()) {
			$this->storeConfig($config, $form);
			ilUtil::sendSuccess('Konfiguration gespeichert', true);
			$this->ctrl->redirect($this, 'configureCourse');
		}
		$form->setValuesByPost();
		$this->tpl->setContent($form->getHTML());
	}

	/**
	 * @param string $active
	 */
	protected function addTabs($active = '') {
		$this->tabs->addTab('configureCourse', 'Basis-Konfiguration', $this->ctrl->getLinkTarget($this, 'configureCourse'));
		$this->tabs->addTab('configureNotifications', 'Benachrichtigungen', $this->ctrl->getLinkTarget($this, 'configureNotifications'));
		if ($active) {
			$this->tabs->setTabActive($active);
		}
	}

}