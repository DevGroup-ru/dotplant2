<?php

namespace app\modules\image\models;

use app\backend\components\Helper;
use app\models\Object;
use app\modules\page\models\Page;
use app\modules\shop\models\Category;
use app\modules\shop\models\Product;
use Yii;
use yii\helpers\Html;

/**
 * This is the model class for table "{{%error_images}}".
 * @property integer $id
 * @property integer $img_id
 * @property string $class_name
 */
class ErrorImage extends \yii\db\ActiveRecord
{
    protected $frontendLink;
    protected $backendLink;
    protected static $classNames;

    protected function getPageLinks($id)
    {
        /** @var Page $model */
        $model = Page::findOne($id);
        if (!is_null($model)) {
            $this->frontendLink = Html::a(
                $model->name,
                [
                    '@article',
                    'model' => $model,
                ]
            );
            $this->backendLink = Html::a(
                $model->name,
                [
                    '/page/backend/edit',
                    'id' => $model->id,
                    'parent_id' => $model->parent_id,
                    'returnUrl' => Helper::getReturnUrl(),
                ]
            );
        }
    }

    protected function getCategoryLinks($id)
    {
        $model = Category::findById($id, null);
        if (!is_null($model)) {
            $this->frontendLink = Html::a(
                $model->name,
                [
                    '@category',
                    'model' => $model,
                    'category_group_id' => isset($model->category)
                        ? $model->category->category_group_id
                        : null,
                ]
            );
            $this->backendLink = Html::a(
                $model->name,
                [
                    '/shop/backend-category/edit',
                    'id' => $model->id,
                    'parent_id' => $model->parent_id,
                    'returnUrl' => Helper::getReturnUrl(),
                ]
            );
        }
    }

    protected function getProductLinks($id)
    {
        $model = Product::findById($id, null);
        if (is_null($model)) {
            return false;
        }
        $this->frontendLink = Html::a(
            $model->name,
            [
                '@product',
                'model' => $model,
                'category_group_id' => isset($model->category)
                    ? $model->category->category_group_id
                    : null,
            ]
        );
        $this->backendLink = Html::a(
            $model->name,
            [
                '/shop/backend-product/edit',
                'id' => $model->id,
                'returnUrl' => Helper::getReturnUrl(),
            ]
        );
    }

    protected function getLinks()
    {
        if (!is_null($this->frontendLink) || !is_null($this->backendLink)) {
            return true;
        }
        /** @var Image $image */
        $image = Image::findById($this->img_id);
        if (is_null($image) || is_null($object = Object::findById($image->object_id))) {
            return false;
        }
        /** @var \app\models\Object $object */
        switch ($object->object_class) {
            case Page::className():
                $this->getPageLinks($image->object_model_id);
                break;
            case Category::className():
                $this->getCategoryLinks($image->object_model_id);
                break;
            case Product::className():
                $this->getProductLinks($image->object_model_id);
                break;
            default:
                return false;
        }
        return true;
    }

    protected static function loadClassNames()
    {
        if (is_null(static::$classNames)) {
            static::$classNames = [
                Image::className() => Yii::t('app', 'Image'),
                Thumbnail::className() => Yii::t('app', 'Thumbnail'),
                Watermark::className() => Yii::t('app', 'Watermark'),
            ];
        }
    }

    public static function getClassNames()
    {
        static::loadClassNames();
        return static::$classNames;
    }

    public function getClassName()
    {
        static::loadClassNames();
        if (isset(static::$classNames[$this->class_name])) {
            return static::$classNames[$this->class_name];
        } else {
            return Yii::t('app', 'Unknown');
        }
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%error_image}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['img_id', 'class_name'], 'required'],
            [['img_id'], 'integer'],
            [['class_name'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'img_id' => Yii::t('app', 'Img ID'),
        ];
    }

    public function getBackendObjectLink()
    {
        return $this->getLinks() ? $this->backendLink : null;
    }

    public function getFrontendObjectLink()
    {
        return $this->getLinks() ? $this->frontendLink : null;
    }
}
