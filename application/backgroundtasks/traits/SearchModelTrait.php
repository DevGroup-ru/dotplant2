<?php

namespace app\backgroundtasks\traits;

use yii\data\ActiveDataProvider;
use yii\db\ActiveQueryInterface;

/**
 * Class SearchModelTrait
 * @package app\backgroundtasks\traits
 * @author evgen-d <flynn068@gmail.com>
 */
trait SearchModelTrait
{

    public function search($params)
    {
        /* @var $query \yii\db\ActiveQuery */
        $query = self::find();

        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
            ]
        );

        /* @var \yii\db\ActiveRecord|SearchModelTrait $this */
        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        foreach ($this->scenarios()['search'] as $field) {
            $this->addCondition($query, $this->tableName(), $field);
        }

        return $dataProvider;
    }

    protected function addCondition(ActiveQueryInterface $query, $tableName, $attribute, $partialMatch = false)
    {
        $value = $this->$attribute;
        if ($partialMatch) {
            $query->andFilterWhere(['like', $tableName . '.' . $attribute, $value]);
        } else {
            $query->andFilterWhere([$tableName . '.' . $attribute => $value]);
        }
    }
}
