<?php

namespace app\modules\shop\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "category_group".
 *
 * @property integer $id
 * @property string $name
 */
class CategoryGroup extends ActiveRecord
{
    private static $firstModel = null;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%category_group}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
        ];
    }

    /**
     * @return CategoryGroup|null
     */
    public static function getFirstModel($createNotExists = true)
    {
        if (null !== static::$firstModel) {
            return static::$firstModel;
        }
        $model = static::find()->orderBy(['id' => SORT_ASC])->limit(1)->one();
        if ($createNotExists && null === $model) {
            $model = new static();
            $model->name = Yii::t('app', 'Shop');
            if (!$model->save()) {
                $model = null;
            }
        }

        return static::$firstModel = $model;
    }
}
?>
