<?php

namespace app\models;

use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;

/**
 * This is the model class for table "{{%slider_handler}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $slider_widget
 * @property string $slider_edit_view_file
 */
class SliderHandler extends \yii\db\ActiveRecord
{
    private static $identity_map = [];

    use \app\traits\FindById;

    public function behaviors()
    {
        return [
            [
                'class' => \devgroup\TagDependencyHelper\ActiveRecordHelper::className(),
            ],
        ];
    }
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%slider_handler}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'slider_widget', 'slider_edit_view_file', 'edit_model'], 'string', 'max' => 255]
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
            'slider_widget' => Yii::t('app', 'Slider Widget'),
            'slider_edit_view_file' => Yii::t('app', 'Slider Edit View File'),
        ];
    }
    /**
    * Returns model using indentity map and cache
    * @param string $id
    * @return SliderHandler|null
    */
    public static function findBySliderId($id)
    {
        if (!isset(SliderHandler::$identity_map[$id])) {
            $cacheKey = SliderHandler::tableName().":$id";
            if (false === $model = Yii::$app->cache->get($cacheKey)) {
                $model = SliderHandler::findById($id);

                if (null !== $model) {
                    Yii::$app->cache->set(
                        $cacheKey,
                        $model,
                        86400,
                        new \yii\caching\TagDependency([
                            'tags' => [
                                ActiveRecordHelper::getObjectTag($model, $model->id)
                            ]
                        ])
                    );
                }
            }
            static::$identity_map[$id] = $model;
        }

        return static::$identity_map[$id];
    }

}
