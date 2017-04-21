<?php namespace SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Form;

use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Config\CourseConfigProvider;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\LearningObjective\LearningObjectiveQuery;
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\User\StudyProgramQuery;

require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('./Services/Form/classes/class.ilMultiSelectInputGUI.php');
require_once('./Modules/Course/classes/class.ilObjCourse.php');
require_once('./Services/User/classes/class.ilUserDefinedFields.php');

/**
 * Class CourseConfigFormGUI
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
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
	 * @param CourseConfigProvider $config
	 * @param LearningObjectiveQuery $objective_query
	 * @param StudyProgramQuery $study_program_query
	 */
	public function __construct(CourseConfigProvider $config,
	                            LearningObjectiveQuery $objective_query,
	                            StudyProgramQuery $study_program_query) {
		parent::__construct();
		$this->config = $config;
		$this->objective_query = $objective_query;
		$this->study_program_query = $study_program_query;
		$this->init();
	}

	protected function init() {
		$this->setTitle('Konfiguration');

		$options = array();
		$definitions = \ilUserDefinedFields::_getInstance()->getDefinitions();
		foreach ($definitions as $field_id => $data) {
			$options[$field_id] = $data['field_name'];
		}
		$udf = new \ilSelectInputGUI('UDF Studienprogramm', 'udf_id_study_program');
		$udf->setInfo('UDF Dropdown Feld, welches die Studiengänge enthält');
		$udf->setOptions($options);
		$udf->setRequired(true);
		$udf->setValue($this->config->get('udf_id_study_program'));

		if ($this->config->get('udf_id_study_program')) {
			$item = new \ilCheckboxInputGUI('UDF Studienprogramm ändern', 'change_mapping_ids');
			$item->setInfo('Achtung: Das Ändern vom UDF wirkt sich auf die gesamte folgende Konfiguration aus');
			$item->addSubItem($udf);
			$this->addItem($item);
			$this->addGeneralConfig();
			$this->addWeightFineConfig();
			$this->addWeightRoughConfig();
		} else {
			$this->addItem($udf);
		}

		$this->addCommandButton('save', 'Speichern');
		$this->addCommandButton('cancel', 'Abbrechen');
	}

	protected function addGeneralConfig() {
		$item = new \ilFormSectionHeaderGUI();
		$item->setTitle('Allgemein');
		$this->addItem($item);

		$item = new \ilMultiSelectInputGUI('Kernbereich', 'learning_objectives_main');
		$item->setInfo('Lernziele, welche dem Kernbereich zugeordnet werden');
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
		$item->setTitle('Erweiterter Bereich');
		$item->setPostVar('learning_objectives_extended');
		$item->setInfo('Lernziele, welche dem erweiterten Bereich zugeordnet werden');
		$item->setValue(json_decode($this->config->get($item->getPostVar()), true));
		$this->addItem($item);

		$item = new \ilNumberInputGUI('Min empfohlene Lernziele', 'min_amount_suggestions');
		$item->setInfo('Minimale Anzahl von Lernziele, welche dem Benutzer empfohlen werden');
		$item->setValue($this->config->get($item->getPostVar()));
		$item->setRequired(true);
		$this->addItem($item);

		$item = new \ilNumberInputGUI('Max empfohlene Lernziele', 'max_amount_suggestions');
		$item->setInfo('Maximale Anzahl von Lernziele, welche dem Benutzer empfohlen werden');
		$item->setValue($this->config->get($item->getPostVar()));
		$item->setRequired(true);
		$this->addItem($item);
	}

	protected function addWeightFineConfig() {
		$item = new \ilFormSectionHeaderGUI();
		$item->setTitle('Feingewichte');
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
			$item->setTitle('Grobgewichte "' . $study_program->getTitle() . '"');
			$this->addItem($item);
			foreach ($this->getObjectives() as $objective) {
				$post_var = 'weight_rough_' . $objective->getId() . '_' . $study_program->getId();
				$item = new \ilNumberInputGUI($objective->getTitle(), $post_var);
				$item->setRequired(true);
				$item->allowDecimals(true);
				$item->setValue($this->config->get($item->getPostVar()));
				$this->addItem($item);
			}
		}

	}

	/**
	 * @return \SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\LearningObjective\LearningObjective[]
	 */
	protected function getObjectives() {
		static $objectives = null;
		if ($objectives !== null) {
			return $objectives;
		}
		$objectives = $this->objective_query->getAll();
		return $objectives;
	}

}