<?php

namespace app\backend\widgets\filterForm;

class filterForm extends \yii\base\Widget
{


    protected $data = [];
    public $fieldName = 'name';
    public $fieldLabel = 'label';
    public $viewFile = 'form';
    public $andConditions = [
        'AND',
        'OR'
    ];
    public $operators = [
        '=',
        '<',
        '>',
        '<=',
        '>=',
        'like',
        '!=',

    ];


    public function run()
    {


        echo $this->render($this->viewFile, [
            'operators' => $this->operators,
            'widgetId' => $this->id,
            'data' => $this->getData(),
            'andConditions' => $this->andConditions,
            'fieldName' => $this->fieldName,
            'fieldLabel' => $this->fieldLabel,
        ]);

    }

    public function getData()
    {

        return $this->data;
    }


}