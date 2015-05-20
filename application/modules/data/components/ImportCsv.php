<?php

namespace app\modules\data\components;

class ImportCsv extends AbstractImportCsv
{
    protected function getCsv($handle)
    {
        return fgetcsv($handle);
    }

    protected function putCsv($handle, $fields)
    {
        fputcsv($handle, $fields);
    }

    protected function putHeader($handle, $fields)
    {
        fputs($handle, "\xEF\xBB\xBF");
        fputcsv($handle, $fields);
    }
}
