<?php

namespace app\modules\core\models;

use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;

/**
 * This is the model class for table "{{%content_block_group}}".
 *
 * @property integer $id
 * @property integer $parent_id
 * @property string $name
 * @property integer $sort_order
 * @property ContentBlockGroup[] $child
 * @property ContentBlock[] $contentBlocks
 */
class ContentBlockGroup extends \yii\db\ActiveRecord
{

    const DEFAULT_PARENT_ID = 1;
    const DELETE_METHOD_ALL = 1;
    const DELETE_METHOD_PARENT_ROOT = 2;

    public $deleteMethod = null;


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%content_block_group}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => ActiveRecordHelper::className(),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['parent_id', 'sort_order', 'deleteMethod'], 'integer'],
            [['name', 'sort_order', 'parent_id'], 'required'],
            [['name'], 'string', 'max' => 250],
            [['sort_order'], 'default', 'value' => 0],
            [['parent_id'], 'default', 'value' => self::DEFAULT_PARENT_ID],
            [['deleteMethod'], 'in', 'range' => [self::DELETE_METHOD_ALL, self::DELETE_METHOD_PARENT_ROOT]]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'parent_id' => Yii::t('app', 'Parent ID'),
            'name' => Yii::t('app', 'Name'),
            'sort_order' => Yii::t('app', 'Sort Order'),
            'deleteMethod' => Yii::t('app', 'Delete Method'),
        ];
    }

    /**
     * @return ContentBlockGroup[]
     */
    public function getChild()
    {
        return $this->hasMany(ContentBlockGroup::className(), ['parent_id' => 'id']);
    }

    /**
     * @return ContentBlock[]
     */
    public function getContentBlocks()
    {
        return $this->hasMany(ContentBlock::className(), ['group_id' => 'id']);
    }

    /**
     * @return string
     */
    public function getNameWithCount()
    {
        return $this->name . ' (' . ContentBlockGroup::find()->where(['group_id' => $this->id])->count() . ')';
    }

    /**
     * @param $id
     * @return null|static
     */
    public static function findById($id)
    {
        return self::findOne(['id' => $id]);
    }

    /**
     * @return bool
     */
    public function beforeDelete()
    {
        if ($this->id == 1) {
            return false;
        }

        return parent::beforeDelete();
    }

    /**
     * @throws \Exception
     */
    public function afterDelete()
    {
        foreach ($this->child as $model) {
            $model->delete();
        }

        foreach ($this->contentBlocks as $block) {
            if ($this->deleteMethod == self::DELETE_METHOD_ALL) {
                $block->delete();
            } else {
                $block->group_id = self::DEFAULT_PARENT_ID;
                $block->save();
            }
        }
        return parent::afterDelete();
    }

}
