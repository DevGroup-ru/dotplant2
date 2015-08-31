<?php

namespace app\commands;

use app\modules\shop\models\Addon;
use app\modules\shop\models\AddonBindings;
use app\modules\shop\models\AddonCategory;
use app\modules\shop\models\Product;
use Yii;
use yii\console\Controller;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

class AddonsController extends Controller
{
    public function actionIndex()
    {
        $db = new \yii\db\Connection([
            'username' => 'root',
            'password' => '',
            'enableSchemaCache' => true,
            'schemaCacheDuration' => 86400,
            'schemaCache' => 'cache',
            'dsn' => 'mysql:host=localhost;dbname=nata84_dveri',
        ]);
        $db->createCommand("SET NAMES utf8")->execute();
        $products = Product::find()->all();
        foreach ($products as $product) {
            /** @var Product $product  */
            $this->updateProductAttributes($product, $db);
        }
    }

    /**
     * @param Product $product
     * @param \yii\db\Connection $db
     */
    private function updateProductAttributes($product, $db)
    {
        $oldProductId = $db->createCommand("SELECT product_id FROM iby1e_jshopping_products WHERE `alias_ru-RU` = :name OR `name_ru-RU` LIKE :name2", [
            ':name' => $product->slug,
            ':name2' => $product->name,
        ])->queryScalar();
        if ($oldProductId > 0) {
            $properties = $db->createCommand("
                SELECT
                iby1e_jshopping_attr.`name_ru-RU` as `property`,
                iby1e_jshopping_attr_values.`name_ru-RU` as `property_value`,
                iby1e_jshopping_products_attr2.price_mod as `price_mod`,
                iby1e_jshopping_products_attr2.addprice as `price`
                FROM `iby1e_jshopping_products_attr2`
                INNER JOIN iby1e_jshopping_attr ON (iby1e_jshopping_attr.attr_id = iby1e_jshopping_products_attr2.attr_id)
                INNER JOIN iby1e_jshopping_attr_values ON (iby1e_jshopping_attr_values.value_id = iby1e_jshopping_products_attr2.attr_value_id)
                WHERE iby1e_jshopping_products_attr2.product_id = $oldProductId
                order by property asc, price asc
                "
            )->queryAll();


            $properties = ArrayHelper::map($properties, 'property_value', function($item){
                return floatval($item['price']);
            }, 'property');
            /*
             * Example of array properties:
array (
  'Добор (комплект)' =>
  array (
    'Без добора' => 0,
    '10 см (2,5 шт)' => 1000,
    '20 см (2,5 шт)' => 1850,
  ),
  'Коробка (комплект)' =>
  array (
    'Без коробки' => 0,
    'С коробкой (2,5 шт)' => 1050,
  ),
  'Наличник (комплект)' =>
  array (
    'Без наличников' => 0,
    'С одной стороны (3шт)' => 630,
    'С двух сторон (5 шт)' => 1050,
  ),
  'Размер' =>
  array (
    'Не выбрано' => 0,
    '2000 х 600 мм' => 0,
    '2000 х 700 мм' => 0,
    '2000 х 800 мм' => 0,
    '2000 х 900 мм' => 500,
  ),
)
             */
            // go through addons
            foreach ($properties as $addonCategoryName => $addons) {
                $category = $this->getCategory($addonCategoryName);
                foreach ($addons as $name => $price) {
                    $name = trim($name);
                    $addon = $this->getAddon($category->id, $name, $price);
                    $this->addBinding($addon, $product);
                }
            }

        } else {
            $this->stderr("Product {$product->id} not found with slug of \"{$product->slug}\"!\n", Console::FG_RED);
        }
    }

    private function getCategory($name)
    {
        $model = AddonCategory::find()->where(['name' => $name])->one();
        if ($model === null) {
            $model = new AddonCategory();
            $model->name = $name;
            $model->save();
        }
        return $model;
    }

    private function getAddon($addon_category_id, $name, $price)
    {
        $model = Addon::find()->where([
            'name' => $name,
            'price' => $price,
            'addon_category_id' => $addon_category_id,
        ])->one();
        if ($model === null) {
            $model = new Addon();
            $model->setAttributes([
                'name' => $name,
                'price' => $price,
                'currency_id' => 1,
                'addon_category_id' => $addon_category_id,
                'price_is_multiplier' => 0,
            ]);
            $model->save();
        }
        return $model;
    }

    private function addBinding(Addon $addon, Product $product)
    {
        echo "PRODUCT: {$product->id} : {$product->name}    addon: {$addon->name}/{$addon->price}\n";
        $binding = AddonBindings::find()
            ->where([
                'appliance_object_id' => 3,
                'object_model_id' => $product->id,
                'addon_id' => $addon->id,
            ])->one();

        if ($binding === null) {
            $product->link('bindedAddons', $addon, [
                'sort_order' => count($product->bindedAddons),
                'appliance_object_id' => 3,
            ]);
        }
    }
}