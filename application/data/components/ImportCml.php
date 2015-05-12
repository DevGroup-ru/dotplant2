<?php
namespace app\data\components;
use app\data\models\OnecId;
use \XMLReader;
use Yii;

class ImportCml extends Import
{
    private $categoryGroupId=1;

    public function setData ()
    {
        $path = Yii::$app->getModule('data')->importDir . '/' . $this->filename;
        $data = [];
        
        if (file_exists($path)) {
            $xml = new XMLReader();
            $xml->open($path);
            switch ($this->object->object_class) {
                case 'app\models\Category':
                    $parser = new CmlGroup2Catalog();
                    $parser->getGroups($xml, 'root');
                    $data = $parser->getData();
                    break;
                case 'app\models\Product':
                    $parser = new CmlGoods2Product();
                    $parser->getProducts($xml, 'root');
                    $parser->setMultipleValuesDelimiter($this->multipleValuesDelimiter);
                    $data = $parser->getData();
                    break;
            }
            $xml->close();
            unlink($path);
        }
        $keys=array();
        $header = array();
        $header[0] = 'category_group_id';
        $i=1;
        foreach ($data as $value)
        {
            foreach ($value as $k=>$v)
            {
            if (!isset($keys[$k])) 
            {
                $header[$i] = $k;
                $keys[$k]=$i;
                $i++;
            }
            }
        }
        
        $result= array();
        $num = count($header);
        $result[0] = $header;
        $i=1;
        foreach ($data as $value)
        {
            $result[$i] = array_fill ( 0 , $num , '' );
            $result[$i][0] = $this->categoryGroupId;
            foreach ($value as $k=>$v)
            {
                $result[$i][$keys[$k]]=$v;
            }
            $i++;
        }
        return $result;
    }

    public function getData ($header, $data)
    {
        return array();
    }
}