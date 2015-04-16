<?php

namespace app\properties\handlers\fileInput;

use app\properties\AbstractPropertyEavModel;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

class FileInputProperty extends \app\properties\handlers\AbstractHandler
{
    protected $widgetClass = '\app\properties\handlers\fileInput\FileInputPropertyWidget';

    /**
     * @param \app\models\Property $property
     * @return bool
     */
    public function changePropertyType(\app\models\Property &$property)
    {
        if (1 !== intval($property->is_eav)) {
            $property->is_eav = 1;
            $property->is_column_type_stored = 0;
            $property->has_static_values = 0;

            return true;
        }

        return parent::changePropertyType($property);
    }

    /**
     * @param \app\models\Property $property
     * @param string $formProperties
     * @param array $values
     * @return array
     */
    public function processValues(\app\models\Property $property, $formProperties = '', $values = [])
    {
        $values = array_filter($values, function($v){
            return !empty($v);
        });

        $directory = FileHelper::normalizePath(\Yii::getAlias('@webroot/upload/'));

        $files = UploadedFile::getInstancesByName($formProperties.'['.$property->key.']');
        foreach ($files as $file) {
            $fileName = $file->baseName.'.'.$file->extension;
            if ($file->saveAs($directory.DIRECTORY_SEPARATOR.$fileName)) {
                $values[] = $fileName;
            }
        }

        return $values;
    }

    /**
     * @param array $params
     * @return false|int
     * @throws \Exception
     */
    public function actionDelete($params = [])
    {
        /** @var \app\models\Property $property */
        $property = $params['property'];
        /** @var \yii\db\ActiveRecord $modelObject */
        $modelObject = new $params['model_name']();
        $modelId = $params['model_id'];

        /** @var \app\models\Object $object */
        $object = $params['object_id'];

        $modelEav = new AbstractPropertyEavModel();
        $modelEav::setTableName($object->eav_table_name);

        $modelEav = $modelEav->find()
            ->where([
                'property_group_id' => $property->property_group_id,
                'key' => $property->key,
                'object_model_id' => $modelId,
                'value' => \Yii::$app->request->post('value')
            ])->one();

        return $modelEav->delete();
    }

    /**
     * @param array $params
     * @return string
     */
    public function actionUpload($params = [])
    {
        /** @var \app\models\Property $property */
        $property = $params['property'];
        /** @var \yii\db\ActiveRecord $modelObject */
        $modelObject = new $params['model_name']();
        $modelId = $params['model_id'];

        /** @var \app\models\Object $object */
        $object = $params['object_id'];

        $formProperties = 'Properties_'. $modelObject->formName() .'_'. $modelId;

        $directory = FileHelper::normalizePath(\Yii::getAlias('@webroot/upload/'));

        $modelEav = new AbstractPropertyEavModel();
        $modelEav::setTableName($object->eav_table_name);
        $modelEav->property_group_id = $property->property_group_id;
        $modelEav->key = $property->key;
        $modelEav->object_model_id = $modelId;

        $files = UploadedFile::getInstancesByName($formProperties.'['.$property->key.']');
        foreach ($files as $file) {
            $fileName = $file->baseName.'.'.$file->extension;
            if (is_file($directory.DIRECTORY_SEPARATOR.$fileName)) {
                $fileName = $file->baseName. substr(md5($fileName.microtime()), 0, 6) .'.'.$file->extension;
            }
            if ($file->saveAs($directory.DIRECTORY_SEPARATOR.$fileName)) {
                $modelEav->isNewRecord = true;
                $modelEav->value = $fileName;
                $modelEav->save();
            }
        }
        unset($modelEav);

        return 'uploaded';
    }
}
?>