<?php

namespace app\modules\data\components;

class ImportExcelCsv extends AbstractImportCsv
{
    protected function getCsv($handle)
    {
        return iconv('windows-1251', 'UTF-8', fgetcsv($handle, null, ';'));
    }

    protected function putCsv($handle, $fields)
    {
        foreach ($fields as $key => $field) {
            $fields[$key] = iconv('UTF-8', 'windows-1251//TRANSLIT', $field);
        }
        fputcsv($handle, $fields, ';');
    }

    protected function putHeader($handle, $fields)
    {
        $this->putCsv($handle, $fields);
    }
}
