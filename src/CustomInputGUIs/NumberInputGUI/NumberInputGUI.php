<?php

namespace SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\CustomInputGUIs\NumberInputGUI;

use ilNumberInputGUI;
use ilTableFilterItem;
use ilToolbarItem;

class NumberInputGUI extends ilNumberInputGUI implements ilTableFilterItem, ilToolbarItem
{
    public function getTableFilterHTML() : string
    {
        return $this->render();
    }

    public function getToolbarHTML() : string
    {
        return $this->render();
    }
}
