<?php

namespace SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\User;
if(file_exists(__DIR__ . "/../../../../../User/UDFDefinition/CascadingSelect/classes/class.ilCascadingSelectSettings.php")) {
    require_once __DIR__ . "/../../../../../User/UDFDefinition/CascadingSelect/classes/class.ilCascadingSelectSettings.php";
}
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Config\CourseConfigProvider;

/**
 * Class StudyProgramQuery
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\User
 */
class StudyProgramQuery
{
    protected CourseConfigProvider $config;
    protected \ilSetting $udf_setting;

    /**
     * @param CourseConfigProvider $config
     */
    public function __construct(CourseConfigProvider $config)
    {
        $this->config = $config;
	$this->udf_setting = new \ilSetting('udfd');
    }

    /**
     * Returns the StudyProgram of the given User
     * @param User $user
     * @return StudyProgram|null
     */
    public function getByUser(User $user): ?StudyProgram
    {
        $data = new \ilUserDefinedData($user->getId());
        $title = $data->get('f_' . $this->config->get('udf_id_study_program'));

        // The data is separated with an arrow, wtf...
        //13.04.2021 Modification DHBW from old master 
          if ($this->isCascadingSelect()) {
        list($_, $title, $_) = array_map('trim', explode("→", $title));
          }
        $filtered = array_filter($this->getAll(), function ($study_program) use ($title) {
            /** @var $study_program StudyProgram */
            return ($study_program->getTitle() == $title);
        });

        return count($filtered) ? array_pop($filtered) : null;
    }

    /**
     * Returns all the StudyPrograms
     * @return StudyProgram[]
     */
    public function getAll(): array
    {
	    
        static $cache = array();
        if (isset($cache[$this->config->getCourse()->getId()])) {
            return $cache[$this->config->getCourse()->getId()];
        }
        $programs = array();
        /** @var \ilUserDefinedFields $udf */
        $udf = \ilUserDefinedFields::_getInstance();
        // Check if field is of type CascadingSelect
        if (!$this->isCascadingSelect()) {
            $data = $udf->getDefinition($this->config->get('udf_id_study_program'));
            foreach ($data['field_values'] as $id => $title) {
                $programs[] = new StudyProgram($id, $title);
            }
        } else {
            $settings = \ilCascadingSelectSettings::getInstance();
            $options = $settings->get('json_' . $this->config->get('udf_id_study_program'));

	        $data = json_decode($options, true);

            $program_titles = array();
            // The study programs are options on the second level of all data available on the first level
            foreach ($data['options'] as $level1) {
                foreach ($level1['options'] as $level2) {
                    $program_titles[] = $level2['name'];
                }
            }
            foreach (array_unique($program_titles) as $id => $title) {
                $programs[] = new StudyProgram($id, $title);
            }
        }

        $cache[$this->config->getCourse()->getId()] = $programs;

        return $programs;
    }

    /**
     * Check if the the UDF field is of type cascading select
     * @return bool
     */
    protected function isCascadingSelect(): bool
    {
        $udf = \ilUserDefinedFields::_getInstance();
        $data = $udf->getDefinition($this->config->get('udf_id_study_program'));
        return ($data['field_type'] === "51");
    }
}