<?php

namespace app\modules\shop\widgets;

use app\modules\shop\models\Product;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use yii\base\Widget;
use yii\caching\TagDependency;

class RelatedProduct extends Widget
{
    /**
     * @property string $viewFile
     * @property Product $product
     * @property array $additional
     */
    public $viewFile = 'related-product/list';
    public $product = null;
    public $additional = [];

    /**
     * @return string
     */
    public function run()
    {
        parent::run();

        if (!$this->product instanceof Product) {
            return '';
        }

        $cacheKey = 'RelatedProduct:' . implode('_', [
            $this->viewFile,
            $this->product,
            json_encode($this->additional),
        ]);

        if (false !== $cache = \Yii::$app->cache->get($cacheKey)) {
            return $cache;
        }

        $result = $this->render($this->viewFile, [
            'model' => $this->product,
            'products' => $this->product->relatedProducts,
            'additional' => $this->additional,
        ]);

        \Yii::$app->cache->set(
            $cacheKey,
            $result,
            0,
            new TagDependency([
                'tags' => [
                    ActiveRecordHelper::getObjectTag(Product::className(), $this->product->id),
                    ActiveRecordHelper::getCommonTag(\app\modules\shop\models\RelatedProduct::className()),
                ]
            ])
        );

        return $result;
    }
}
