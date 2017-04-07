<?php namespace SRAG\ILIAS\Plugins\AutoLearningObjectives\Config;

use SRAG\ILIAS\Plugins\AutoLearningObjectives\LearningObjective\LearningObjectiveCourse;

require_once('./Services/Table/classes/class.ilTable2GUI.php');
require_once('./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php');

/**
 * Class LearningObjectiveCourseTableGUI
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\AutoLearningObjectives\LearningObjective
 */
class LearningObjectiveCourseTableGUI extends \ilTable2GUI {

	protected $ctrl;

	/**
	 * @param $a_parent_obj
	 */
	public function __construct($a_parent_obj) {
		global $ilCtrl;
		parent::__construct($a_parent_obj, '', '');
		$this->ctrl = $ilCtrl;
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate('tpl.row_generic.html', './Customizing/global/plugins/Services/Cron/CronHook/LearningObjectiveSuggestions');
		$this->setTitle('Lernzielorientierte Kurse');
		$this->addColumns();
	}

	/**
	 * @param LearningObjectiveCourse[] $courses
	 */
	public function setCourses(array $courses) {
		$data = array();
		foreach ($courses as $course) {
			$data[] = array(
				'ref_id' => $course->getRefId(),
				'title' => $course->getTitle(),
			);
		}
		$this->setData($data);
	}


	protected function addColumns() {
		foreach ($this->getSelectableColumns() as $column => $data) {
			if ($this->isColumnSelected($column)) {
				$this->addColumn($data['txt'], $column);
			}
		}
		$this->addColumn('Aktionen');
	}

	protected function fillRow($a_set) {
		foreach (array_keys($this->getSelectableColumns()) as $column) {
			if (!$this->isColumnSelected($column)) {
				continue;
			}
			$this->tpl->setCurrentBlock('td');
			$this->tpl->setVariable('VALUE', $a_set[$column] ? $a_set[$column] : '&nbsp;');
			$this->tpl->parseCurrentBlock();
		}
		$list = new \ilAdvancedSelectionListGUI();
		static $id = 0;
		$list->setId(++$id);
		$this->ctrl->setParameter($this->parent_obj, 'course_ref_id', $a_set['ref_id']);
		$list->addItem('Konfigurieren', '', $this->ctrl->getLinkTarget($this->parent_obj, 'configureCourse'));
		$this->ctrl->clearParameters($this->parent_obj);
		$list->setListTitle('Aktionen');
		$this->tpl->setCurrentBlock('td');
		$this->tpl->setVariable('VALUE', $list->getHTML());
		$this->tpl->parseCurrentBlock();
	}


	function getSelectableColumns() {
		return array(
			'ref_id' => array('txt' => 'Ref-ID', 'default' => true),
			'title' => array('txt' => 'Titel', 'default' => true),
		);
	}


}