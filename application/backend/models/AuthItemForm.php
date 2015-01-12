<?php

namespace app\backend\models;

use Yii;
use yii\base\Model;
use yii\rbac\Item;

/**
 * LoginForm is the model behind the login form.
 */
class AuthItemForm extends Model
{
    public $name;
    public $oldname;
    public $type;
    public $description = '';
    public $ruleName = null;
    public $isNewRecord = false;

    public $children = [];

    private $errorMessage = '';

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['name', 'type'], 'required'],
            ['name', 'check'],
            ['isNewRecord', 'boolean'],
        ];
    }

    public function scenarios()
    {
        return [
            'default' => ['name', 'oldname', 'type', 'description', 'ruleName', 'children'],
        ];
    }

    public function check($attribute, $params)
    {
        if (((strlen($this->oldname) == 0) || ($this->oldname != $this->name)) &&
            ((\Yii::$app->getAuthManager()->getRole($this->$attribute) !== null) ||
                \Yii::$app->getAuthManager()->getPermission($this->$attribute) !== null)) {
            $this->addError($attribute, 'Duplicate Item "'.$this->$attribute.'"');
        }
    }

    public function createItem()
    {
        $item = new Item(
            [
                'name' => $this->name,
                'type' => $this->type,
                'description' => $this->description,
                'ruleName' => trim($this->ruleName) ? trim($this->ruleName) : null,
            ]
        );
        Yii::$app->getAuthManager()->add($item);
        foreach ($this->children as $value) {
            try {
                Yii::$app->getAuthManager()->addChild($item, new Item(['name' => $value]));
            } catch (\Exception $ex) {
                $this->errorMessage .= Yii::t('app', "Item <strong>{value}</strong> is not assigned:", [
                        'value' => $value,
                    ])
                    . " " . $ex->getMessage() . "<br />";
            }
        }
        return $item;
    }

    public function updateItem()
    {
        $item = new Item();
        $item->name = $this->name;
        $item->type = $this->type;
        $item->description = $this->description;
        $item->ruleName = trim($this->ruleName) ? trim($this->ruleName) : null;
        Yii::$app->getAuthManager()->update($this->oldname, $item);
        $children = Yii::$app->getAuthManager()->getChildren($item->name);
        foreach ($children as $value) {
            $key = array_search($value->name, $this->children);
            if ($key === false) {
                Yii::$app->getAuthManager()->removeChild($item, $value);
            } else {
                unset($this->children[$key]);
            }
        }
        foreach ($this->children as $value) {
            try {
                Yii::$app->getAuthManager()->addChild($item, new Item(['name' => $value]));
            } catch (\Exception $ex) {
                $this->errorMessage .= Yii::t('app', "Item <strong>{value}</strong> is not assigned:", [
                        'value' => $value,
                    ])
                    . " " . $ex->getMessage() . "<br />";
            }
        }
        return $item;
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function attributeLabels()
    {
        return [
            'name' => Yii::t('app', 'Name'),
            'oldname' => Yii::t('app', 'Old Name'),
            'type' => Yii::t('app', 'Type'),
            'description' => Yii::t('app', 'Description'),
            'ruleName' => Yii::t('app', 'Biz Rule'),
            'children' => Yii::t('app', 'Children'),
        ];
    }
}
