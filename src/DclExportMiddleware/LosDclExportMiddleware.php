<?php
include_once "./Customizing/global/plugins/Services/WebServices/SoapHook/DataCollectionSOAPServices/vendor/autoload.php";
use SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Cron\SendSuggestionsCronJob;
use srag\Plugins\DataCollectionSOAPServices\ExportDataCollectionExtendDataMiddleware;

class LosDclExportMiddleware implements ExportDataCollectionExtendDataMiddleware {

    public function process(ilDclTable $table, ilExcel $worksheet, ilDclBaseRecordModel $record, &$row, &$col, $field_id)
    {
        $colstart = $col;
        $worksheet->setCell(1, $col,"Login");
        $col++;
        $worksheet->setCell(1, $col,"Percentage DET");

        $col = $colstart;
        $worksheet->setCell($row, $col,ilObjUser::_lookupLogin($record->getOwner()));
        $col++;
        $worksheet->setCell($row, $col,SendSuggestionsCronJob::getTestUserResult($record->getOwner()));
        $col++;
    }
}