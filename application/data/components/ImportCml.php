<?php
namespace app\data\components;
use app\data\models\OnecId;
use \XMLReader;
use Yii;

class ImportCml extends Import
{

    public function setData ()
    {
        $path = Yii::$app->getModule('data')->importDir . '/' . $this->filename;
        $data = [];
        
        if (file_exists($path)) {
            $xml = new XMLReader();
            $xml->open($path);
            switch ($this->object->object_class) {
                case 'Category':
                    $parser = new CmlGroup2Catalog();
                    $parser->getGroups($xml, 'root');
                    $data = $parser->getData();
                    break;
                case 'Product':
                    $parser = new CmlGoods2Product();
                    $parser->getProducts($xml, 'root');
                    $parser->set($this->multipleValuesDelimiter);
                    $data = $parser->getData();
                    break;
            }
            $xml->close();
            unlink($path);
        }
        
        return $data;
    }

    public function setData ()
    {
        return array();
    }
}