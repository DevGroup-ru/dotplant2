<?php

namespace app\modules\data\components;

use app\models\Property;
use app\models\Object;
use app\modules\shop\models\Product;
use yii\db\Expression;
use app\models\PropertyStaticValues;
use app\models\ObjectStaticValues;
use Yii;

abstract class AbstractImportCsv extends Import
{
    abstract protected function getCsv($handle);
    abstract protected function putCsv($handle, $fields);
    abstract protected function putHeader($handle, $fields);

    public function setData()
    {
        $path = Yii::$app->getModule('data')->importDir . '/' . $this->filename;
        $data = [];

        $file = fopen($path, 'r');
        if (false !== $file) {
            $header = true;
            while (($row = $this->getCsv($file)) !== false) {
                if ($header) {
                    if (substr($row[0], 0, 3) == "\xEF\xBB\xBF") {
                        $row[0] = substr($row[0], 3);
                    }
                    $header = false;
                }
                $data[] = $row;
            }
            fclose($file);
        }

        if (file_exists($path)) {
            unlink($path);
        }

        return $data;
    }

    public function getData($header, $data)
    {
        $output = fopen(Yii::$app->getModule('data')->exportDir . '/' . $this->filename, 'w');

        $this->putHeader($output, $header);

        foreach ($data as $k => $row) {
            $this->putCsv($output, $row);
        }

        fclose($output);

        return true;
    }
}
