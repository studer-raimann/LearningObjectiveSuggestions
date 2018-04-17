<?php namespace SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Form;

use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Config\CourseConfigProvider;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\LearningObjective\LearningObjective;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\LearningObjective\LearningObjectiveQuery;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\User\StudyProgramQuery;

/**
 * Class CourseConfigFormGUI
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Form
 */
class CourseConfigFormGUI extends \ilPropertyFormGUI {

	/**
	 * @var CourseConfigProvider
	 */
	protected $config;
	/**
	 * @var LearningObjectiveQuery
	 */
	protected $objective_query;
	/**
	 * @var StudyProgramQuery
	 */
	protected $study_program_query;
	/**
	 * @var \ilLearningObjectiveSuggestionsPlugin
	 */
	protected $pl;


	/**
	 * @param CourseConfigProvider   $config
	 * @param LearningObjectiveQuery $objective_query
	 * @param StudyProgramQuery      $study_program_query
	 */
	public function __construct(CourseConfigProvider $config, LearningObjectiveQuery $objective_query, StudyProgramQuery $study_program_query) {
		parent::__construct();
		$this->config = $config;
		$this->objective_query = $objective_query;
		$this->study_program_query = $study_program_query;
		$this->pl = \ilLearningObjectiveSuggestionsPlugin::getInstance();
		$this->init();
	}


	protected function init() {
		$this->setTitle($this->pl->txt("configuration"));

		$options = array();
		$definitions = \ilUserDefinedFields::_getInstance()->getDefinitions();
		foreach ($definitions as $field_id => $data) {
			$options[$field_id] = $data['field_name'];
		}
		$udf = new \ilSelectInputGUI($this->pl->txt("udf_study_program"), 'udf_id_study_program');
		$udf->setInfo($this->pl->txt("udf_study_program_info"));
		$udf->setOptions($options);
		$udf->setRequired(true);
		$udf->setValue($this->config->get('udf_id_study_program'));

		if ($this->config->get('udf_id_study_program')) {
			$item = new \ilCheckboxInputGUI($this->pl->txt("change_udf_study_program"), 'change_mapping_ids');
			$item->setInfo($this->pl->txt("change_udf_study_program_info"));
			$item->addSubItem($udf);
			$this->addItem($item);
			$this->addGeneralConfig();
			$this->addWeightFineConfig();
			$this->addWeightRoughConfig();
		} else {
			$this->addItem($udf);
		}

		$this->addCommandButton(\ilLearningObjectiveSuggestionsConfigGUI::CMD_SAVE, $this->pl->txt("save"));
		$this->addCommandButton(\ilLearningObjectiveSuggestionsConfigGUI::CMD_CANCEL, $this->pl->txt("cancel"));
	}


	protected function addGeneralConfig() {
		$item = new \ilFormSectionHeaderGUI();
		$item->setTitle($this->pl->txt("general"));
		$this->addItem($item);

		$item = new \ilMultiSelectInputGUI($this->pl->txt("objectives_main"), 'learning_objectives_main');
		$item->setInfo($this->pl->txt("objectives_main_info"));
		$item->setRequired(true);
		$item->setWidth(100);
		$item->setWidthUnit('%');
		$item->setHeight(150);
		$objectives = $this->getObjectives();
		$options = array();
		foreach ($objectives as $objective) {
			$options[$objective->getId()] = $objective->getTitle();
		}
		$item->setOptions($options);
		$item->setValue(json_decode($this->config->get($item->getPostVar()), true));
		$this->addItem($item);

		$item = clone $item;
		$item->setTitle($this->pl->txt("objectives_extended"));
		$item->setPostVar('learning_objectives_extended');
		$item->setInfo($this->pl->txt("objectives_extended_info"));
		$item->setValue(json_decode($this->config->get($item->getPostVar()), true));
		$this->addItem($item);

		$item = new \ilNumberInputGUI($this->pl->txt("min_amount_suggestions"), 'min_amount_suggestions');
		$item->setInfo($this->pl->txt("min_amount_suggestions_info"));
		$item->setValue($this->config->get($item->getPostVar()));
		$item->setRequired(true);
		$this->addItem($item);

		$item = new \ilNumberInputGUI($this->pl->txt("max_amount_suggestions"), 'max_amount_suggestions');
		$item->setInfo($this->pl->txt("max_amount_suggestions_info"));
		$item->setValue($this->config->get($item->getPostVar()));
		$item->setRequired(true);
		$this->addItem($item);

		$item = new \ilNumberInputGUI($this->pl->txt("bias"), 'bias');
		$item->setValue($this->config->get($item->getPostVar()));
		$item->setRequired(true);
		$this->addItem($item);

		$item = new \ilNumberInputGUI($this->pl->txt("offset"), 'offset');
		$item->setValue($this->config->get($item->getPostVar()));
		$item->setRequired(true);
		$this->addItem($item);

		$item = new \ilNumberInputGUI($this->pl->txt("steps"), 'steps');
		$item->setValue($this->config->get($item->getPostVar()));
		$item->setRequired(true);
		$this->addItem($item);
	}


	protected function addWeightFineConfig() {
		$item = new \ilFormSectionHeaderGUI();
		$item->setTitle($this->pl->txt("weight_fine"));
		$this->addItem($item);
		foreach ($this->getObjectives() as $objective) {
			$item = new \ilNumberInputGUI($objective->getTitle(), 'weight_fine_' . $objective->getId());
			$item->setRequired(true);
			$item->allowDecimals(true);
			$item->setValue($this->config->get($item->getPostVar()));
			$this->addItem($item);
		}
	}


	protected function addWeightRoughConfig() {
		foreach ($this->study_program_query->getAll() as $study_program) {
			$item = new \ilFormSectionHeaderGUI();
			$item->setTitle($this->pl->txt("weight_rough") . ' "' . $study_program->getTitle() . '"');
			$this->addItem($item);
			foreach ($this->getObjectives() as $objective) {
				$post_var = 'weight_rough_' . $objective->getId() . '_' . $study_program->getId();
				$item = new \ilNumberInputGUI($objective->getTitle(), $post_var);
				$item->setRequired(true);
				$item->allowDecimals(true);
				$value = $this->config->get($item->getPostVar());
				$item->setValue($value === NULL ? 100 : $value);
				$this->addItem($item);
			}
		}
	}


	/**
	 * @return LearningObjective[]
	 */
	protected function getObjectives() {
		static $objectives = NULL;
		if ($objectives !== NULL) {
			return $objectives;
		}
		$objectives = $this->objective_query->getAll();

		return $objectives;
	}
}