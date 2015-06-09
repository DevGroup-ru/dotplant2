<?php

namespace app\modules\seo\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "sitemap".
 *
 * @property string $uid
 * @property string $url
 */
class Sitemap extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%sitemap}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'url'], 'required'],
            [['url'], 'string'],
            [['uid'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'uid' => 'Uid',
            'url' => 'Url',
        ];
    }
}
