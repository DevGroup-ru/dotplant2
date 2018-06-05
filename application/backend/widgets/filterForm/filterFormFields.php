<?php
/**
 * Created by PhpStorm.
 * User: dmitriy
 * Date: 17.03.15
 * Time: 13:16
 */

namespace app\backend\widgets\filterForm;

use app\models\BaseObject;
use Yii;

class filterFormFields extends filterForm {

    public $fieldName = 'field';
    public $objectId = null;


    public function init() {

        $this->fieldLabel = Yii::t('app', 'Filter By Fields');

        return parent::init();
    }


    public function getData() {

        $objectClass = BaseObject::findById($this->objectId)->object_class;
        $object = new $objectClass;
        $this->data = $object->attributeLabels();
        return parent::getData();
    }

}