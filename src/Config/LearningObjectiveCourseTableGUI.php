<?php namespace SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Config;

use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\LearningObjective\LearningObjectiveCourse;

/**
 * Class LearningObjectiveCourseTableGUI
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Config
 */
class LearningObjectiveCourseTableGUI extends \ilTable2GUI {

	/**
	 * @var \ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var \ilLearningObjectiveSuggestionsPlugin
	 */
	protected $pl;


	/**
	 * @param $a_parent_obj
	 */
	public function __construct($a_parent_obj) {
		global $DIC;
		$this->pl = \ilLearningObjectiveSuggestionsPlugin::getInstance();
		parent::__construct($a_parent_obj, '', '');
		$this->ctrl = $DIC->ctrl();
		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
		$this->setRowTemplate('tpl.row_generic.html', $this->pl->getDirectory());
		$this->setTitle($this->pl->txt("courses"));
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
		$this->addColumn($this->pl->txt("actions"));
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
		$list->setId(++ $id);
		$this->ctrl->setParameter($this->parent_obj, 'course_ref_id', $a_set['ref_id']);
		$list->addItem($this->pl->txt("configurate"), '', $this->ctrl->getLinkTarget($this->parent_obj, \ilLearningObjectiveSuggestionsConfigGUI::CMD_CONFIGURE_COURSE));
		$list->addItem($this->pl->txt("delete_learning_objective_course"), '', $this->ctrl->getLinkTarget($this->parent_obj, \ilLearningObjectiveSuggestionsConfigGUI::CMD_CONFIRM_DELETE_COURSE_CONFIG));
		$this->ctrl->clearParameters($this->parent_obj);
		$list->setListTitle($this->pl->txt("actions"));
		$this->tpl->setCurrentBlock('td');
		$this->tpl->setVariable('VALUE', $list->getHTML());
		$this->tpl->parseCurrentBlock();
	}


	function getSelectableColumns() {
		return array(
			'ref_id' => array( 'txt' => $this->pl->txt("ref_id"), 'default' => true ),
			'title' => array( 'txt' => $this->pl->txt("title"), 'default' => true ),
		);
	}
}