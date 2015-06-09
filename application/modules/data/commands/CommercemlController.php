<?php

namespace app\modules\data\commands;

use app\modules\data\components\commerceml\XmlFileReader;
use yii\base\InvalidParamException;
use yii\console\Controller;
use yii\helpers\Json;

class CommercemlController extends Controller
{
    public function actionImport($params = null)
    {
        try {
            $params = Json::decode($params);
        } catch (InvalidParamException $e) {
            echo 'Wrong input parameters.';
            return 1;
        }

        if (empty($params['files'])) {
            return 1;
        }

        $ts = microtime(true);

        $files = [];
        foreach ($params['files'] as $file) {
            $xmlReader = new XmlFileReader($file);
            if (XmlFileReader::FILETYPE_IMPORT === $xmlReader->fileType()) {
                array_unshift($files, $file);
            } else {
                $files[] = $file;
            }
            unset($xmlReader);
        }

        foreach ($files as $file) {
            $xmlReader = new XmlFileReader($file);
            $xmlReader->parse();
            unset($xmlReader);
            unlink($file);
        }

        echo sprintf('Task working time: %s', microtime(true) - $ts);

        return 0;
    }
}
?>