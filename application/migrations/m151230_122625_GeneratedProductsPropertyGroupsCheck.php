<?php

use app\models\Object;
use app\models\ObjectPropertyGroup;
use app\modules\shop\models\Product;
use yii\helpers\Json;
use yii\db\Migration;

class m151230_122625_GeneratedProductsPropertyGroupsCheck extends Migration
{
    public function up()
    {
        $products = Product::findAll(['parent_id' => 0]);
        foreach ($products as $product) {
            $object = Object::getForClass(Product::className());
            $gid = Json::decode($product->option_generate)['group'];
            $cid = Product::findAll(['parent_id' => $product->id]);
            foreach ($cid as $subProduct) {
                $q = ObjectPropertyGroup::findAll(
                    'object_model_id = :object_model_id and object_id = :object_id and property_group_id = :property_group_id',
                    [
                        'object_model_id' => $subProduct->id,
                        'object_id' => $object->id,
                        'property_group_id' => $gid
                    ]
                );
                if (count($q) === 0) {
                    $opg = new ObjectPropertyGroup();
                    $opg->object_id = $object->id;
                    $opg->object_model_id = $subProduct->id;
                    $opg->property_group_id = $gid;
                    if ($opg->save()) {
                        echo 'Insert: ' . $opg->object_model_id . ' with property_group_id = ' . $opg->property_group_id . "\n";
                    } else {
                        echo 'Insert error' . "\n";
                        print_r($opg->getErrors());
                    }
                }
            }
        }
    }

    public function down()
    {
        echo "m151230_122625_GeneratedProductsPropertyGroupsCheck down()\n";
        return true;
    }
}
