<?php

namespace app\backend\widgets;

use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use app;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\caching\TagDependency;
use app\modules\shop\models\WarehouseProduct;
use app\modules\shop\models\Warehouse;


/**
 * Widget WarehousesRemains renders input block for specifying product remains on each of active warehouse
 * @package app\backend\widgets
 */
class WarehousesRemains extends Widget
{
    /**
     * @var app\modules\shop\models\Product Product model
     */
    public $model = null;

    /**
     * @inheritdoc
     */
    public function run()
    {
        if ($this->model === null) {
            throw new InvalidConfigException("Model should be set for WarehousesRemains widget");
        }

        $state = $this->model->getWarehousesState();

        $activeWarehousesIds = Warehouse::activeWarehousesIds();
        $remains = [];
        foreach ($state as $remain) {
            $remains[$remain->warehouse_id] = $remain;
            if(($key = array_search($remain->warehouse_id, $activeWarehousesIds)) !== false) {
                unset($activeWarehousesIds[$key]);
            }
        }

        // if we have new warehouses that not represented in warehouses state
        if (count($activeWarehousesIds) > 0) {
            foreach ($activeWarehousesIds as $id) {
                // create new record with default values
                $remain = new WarehouseProduct();
                $remain->warehouse_id = $id;
                $remain->product_id = $this->model->id;
                $remain->save();

                // add to remains
                $remains[$remain->warehouse_id] = $remain;
            }
            TagDependency::invalidate(Yii::$app->cache, ActiveRecordHelper::getObjectTag($this->model->className(), $this->model->id));
        }

        return $this->render(
            'warehouses-remains',
            [
                'model' => $this->model,
                'remains' => $remains,
            ]
        );
    }
}