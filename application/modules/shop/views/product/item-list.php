<?php
/**
 * @var Product[] $products
 */

use app\modules\shop\models\Product;
use yii\helpers\Url;

foreach ($products as $product) {
    if ($this->beginCache('Product-items:' . ':' . $product->id, [
        'duration' => 86400,
        'dependency' => new \yii\caching\TagDependency([
            'tags' => $product->getCacheTags(),
        ])
    ])
    ) {
        $url = Url::to(
            [
                '@product',
                'model' => $product,
            ]
        );

        echo $this->render('item-row',
            ['product' => $product, 'url' => $url]);
        $this->endCache();
    }
}