<?php

namespace app\modules\data\components;


class ImportXlsx extends Import
{
    public $fileType = 'xlsx';

    /*
     * Export
     */
    public function getData($header, $data)
    {
        $filename = \Yii::$app->getModule('data')->exportDir . DIRECTORY_SEPARATOR . $this->filename;

        array_unshift($data, $header);

        $excel = new \PHPExcel();

        $excel_cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
        $excel_cacheSettings = ['memoryCacheSize ' => '128MB'];
        \PHPExcel_Settings::setCacheStorageMethod($excel_cacheMethod, $excel_cacheSettings);

        $excel->setActiveSheetIndex(0);
        $excel->getActiveSheet()->fromArray($data, null, 'A1');

        $excel_provider = [
            'xlsx' => 'Excel2007',
            'xls' => 'Excel5',
        ];
        $excel_provider = isset($excel_provider[$this->fileType]) ? $excel_provider[$this->fileType] : 'Excel2007';

        $excel_writer = \PHPExcel_IOFactory::createWriter($excel, $excel_provider);
        $excel_writer->save($filename);

        return true;
    }

    /*
     * Import
     */
    public function setData()
    {
        $excel_cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
        $excel_cacheSettings = ['memoryCacheSize ' => '128MB'];
        \PHPExcel_Settings::setCacheStorageMethod($excel_cacheMethod, $excel_cacheSettings);

        $excel = \PHPExcel_IOFactory::load(\Yii::$app->getModule('data')->importDir . '/' . $this->filename);
        $data = $excel->getActiveSheet()->toArray();

        unset($excel);

        return $data;
    }
}