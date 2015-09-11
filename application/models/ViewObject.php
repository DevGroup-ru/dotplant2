<?php

namespace app\models;

use Yii;
use yii\caching\TagDependency;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "view_object".
 *
 * @property integer $id
 * @property integer $object_id
 * @property integer $object_model_id
 * @property integer $view_id
 * @property integer $child_view_id
 */
class ViewObject extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%view_object}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['view_id', 'child_view_id'], 'default', 'value' => 1],
            [['object_id', 'object_model_id', 'view_id', 'child_view_id'], 'required'],
            [['object_id', 'object_model_id', 'view_id', 'child_view_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'object_id' => Yii::t('app', 'Object ID'),
            'object_model_id' => Yii::t('app', 'Object Model ID'),
            'view_id' => Yii::t('app', 'View'),
            'child_view_id' => Yii::t('app', 'Child view')
        ];
    }

    /**
     * Поиск представления по модели
     *
     * @param Object $model
     * @return string|null Возвращает имя файла или null, если ничего не найдено
     */
    public static function getViewByModel($model = null, $childView = false)
    {
        if ((null === $model) || !is_object($model)) {
            return null;
        }

        if (null === $object = Object::getForClass($model::className())) {
            return null;
        }

        $cacheKey = "View:Object:ModelId" . $object->id . ":" . $model->id;
        $viewObject = Yii::$app->cache->get($cacheKey);
        if ($viewObject === false) {

            $viewObject = static::find()
                ->where(
                    [
                        'object_id' => $object->id,
                        'object_model_id' => $model->id,
                    ]
                )->one();

        }
        if ($viewObject !== null) {
            Yii::$app->cache->set(
                $cacheKey,
                $viewObject,
                86400,
                new TagDependency([
                    'tags' => [
                        \devgroup\TagDependencyHelper\ActiveRecordHelper::getObjectTag($viewObject, $viewObject->id),
                        \devgroup\TagDependencyHelper\ActiveRecordHelper::getObjectTag($object, $object->id),
                        \devgroup\TagDependencyHelper\ActiveRecordHelper::getObjectTag($model, $model->id)
                    ]
                ])
            );
            return View::getViewById($childView ? $viewObject->child_view_id : $viewObject->view_id);
        } else {
            return null;
        }

    }

    /**
     * Поиск связи по модели
     *
     * @param \yii\db\ActiveRecord $model
     * @param boolean $forceDefault Флаг для принудительного возврата модели
     * @return \yii\db\ActiveRecord|null Возвращает модель или null, если ничего не найдено
     */
    public static function getByModel($model = null, $forceDefault = false)
    {
        if ((null === $model) || !is_object($model)) {
            return null;
        }

        if (null === $object = Object::getForClass($model::className())) {
            return null;
        }

        if (
            null === $result = static::find()->where(
                [
                    'object_id' => $object->id,
                    'object_model_id' => $model->id,
                ]
            )->one()
        ) {
            if ($forceDefault) {
                $result = new static;
                $result->object_id = $object->id;
                $result->object_model_id = $model->id;
                $result->view_id = 1;
                $result->child_view_id = 1;
            }
        }

        return $result;
    }

    public static function deleteByViewId($view_id = null)
    {
        if (null === $view_id) {
            return null;
        }

        return static::deleteAll(['view_id' => $view_id]);
    }
}
