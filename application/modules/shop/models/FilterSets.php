<?php

namespace app\modules\shop\models;

use app\models\Property;
use app\traits\SortModels;
use Yii;

/**
 * This is the model class for table "{{%filter_sets}}".
 *
 * @property integer $id
 * @property integer $category_id
 * @property integer $sort_order
 * @property integer $property_id
 * @property integer $is_filter_by_price
 * @property integer $delegate_to_children
 */
class FilterSets extends \yii\db\ActiveRecord
{
    use SortModels;
    /**
     * @inheritdoc
     */
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
        return '{{%filter_sets}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category_id'], 'required'],
            [['category_id', 'sort_order', 'property_id', 'is_filter_by_price', 'delegate_to_children'], 'integer'],
            [['category_id', 'property_id'], 'unique', 'targetAttribute'=>['category_id', 'property_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'category_id' => Yii::t('app', 'Category ID'),
            'sort_order' => Yii::t('app', 'Sort Order'),
            'property_id' => Yii::t('app', 'Property ID'),
            'is_filter_by_price' => Yii::t('app', 'Is Filter By Price'),
            'delegate_to_children' => Yii::t('app', 'Delegate To Children'),
        ];
    }

    public static function getForCategoryId($categoryId)
    {
        $category = Category::findById($categoryId);
        if ($category === null) {
            return false;
        }
        $categoryIds = $category->getParentIds();


        $filter_sets = FilterSets::find()
            ->where(['in', 'category_id', $categoryIds])
            ->andWhere(['delegate_to_children' => 1])
            ->orWhere(['category_id' => $category->id])
            ->orderBy(['sort_order' => SORT_ASC])
            ->all();

        return $filter_sets;
    }

    /**
     * @return Property
     */
    public function getProperty()
    {
        return Property::findById($this->property_id);
    }
}
