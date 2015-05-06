<?php

namespace app\modules\seo\models;

use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "seo_meta".
 *
 * @property string $key
 * @property string $name
 * @property integer $content
 */
class Meta extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%seo_meta}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['key', 'name', 'content'], 'required'],
            [['key'], 'unique'],
            [['key', 'name', 'content'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'key' => \Yii::t('app', 'Key'),
            'name' => \Yii::t('app', 'Name'),
            'content' => \Yii::t('app', 'Content'),
        ];
    }

    /**
     * Search meta tags
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $this->load($params);
        $query = self::find();
        foreach ($this->attributes as $name => $value) {
            if (!empty($value)) {
                $query->andWhere("`$name` LIKE :$name", [":$name" => "%$value%"]);
            }
        }
        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'pagination' => [
                    'pageSize' => 10,
                ],
            ]
        );
        return $dataProvider;
    }
}
