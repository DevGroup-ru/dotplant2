<?php

namespace app\data\components;

use app\data\models\OnecId;
use \XMLReader;
use Yii;

class ImportCml extends Import
{

	public function setData()
	{
		$path = Yii::$app->getModule('data')->importDir . '/' . $this->filename;
		$data = [];
	
		if (file_exists($path)) {
			$xml = new XMLReader ();
			$xml->open($path);
			$parser = new CmlGroup2Catalog();
			$parser->getGroups($xml, 'root');
			$data = $parser->getData();
			$xml->close ();
			unlink($path);
		}
	
		return $data;
	}
	public function setData(){
		return array();
	}
}