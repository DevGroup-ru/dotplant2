<?php

use app\modules\image\models\Image;
use app\modules\shop\models\Category;
use app\modules\shop\models\Product;
use app\models\PropertyGroup;
use app\models\Property;
use app\models\PropertyStaticValues;
use app\models\Object;
use app\models\ObjectStaticValues;
use app\models\ObjectPropertyGroup;
use app\models\PropertyHandler;
use app\components\Helper;
use yii\db\Migration;

class m150605_094805_demo_data extends Migration
{
    protected $products = [];
    protected $properties = [];
    protected $values = [];
    protected $textHandlerId;
    protected $selectHandlerId;

    protected function getKey($name, $length = 20, $delimiter = '_')
    {
        if ($delimiter == '_') {
            $name = str_replace('-', '_', Helper::createSlug($name));
        }
        return mb_strlen($name) > $length
            ? mb_substr($name, 0, $length - 5). $delimiter . mb_substr(md5($name), 0, 4)
            : $name;
    }

    protected function saveEav($id, $groupId, $name, $value)
    {
        $key = $this->getKey($name);
        if (!isset($this->properties[$key])) {
            $this->insert(
                Property::tableName(),
                [
                    'property_group_id' => $groupId,
                    'name' => $name,
                    'key' => $key,
                    'property_handler_id' => $this->textHandlerId,
                    'handler_additional_params' => '{}',
                    'is_eav' => 1,
                    'has_slugs_in_values' => 0,
                ]
            );
            $this->properties[$key] = $this->db->lastInsertID;
        }
        $this->insert(
            '{{%product_eav}}',
            [
                'object_model_id' => $id,
                'property_group_id' => $groupId,
                'key' => $key,
                'value' => $value,
            ]
        );
    }

    protected function saveStatic($id, $key, $value)
    {
        if (!isset($this->values[$value])) {
            $value = trim($value, '/ ');
            $this->insert(
                PropertyStaticValues::tableName(),
                [
                    'property_id' => $this->properties[$key],
                    'name' => $value,
                    'value' => $value,
                    'slug' => Helper::createSlug($value),
                ]
            );
            $this->values[$value] = $this->db->lastInsertID;
        }
        $this->insert(
            ObjectStaticValues::tableName(),
            [
                'object_id' => Object::getForClass(Product::className())->id,
                'object_model_id' => $id,
                'property_static_value_id' => $this->values[$value],
            ]
        );
    }

    public function up()
    {
        mb_internal_encoding(Yii::$app->getModule('core')->internalEncoding);
        $data = include __DIR__ . DIRECTORY_SEPARATOR . 'demo-data.php';
        $productObject = Object::getForClass(Product::className());
        /** @var PropertyHandler $handler */
        $handler = PropertyHandler::findOne(['handler_class_name' => 'app\properties\handlers\text\TextProperty']);
        if (!is_null($handler)) {
            $this->textHandlerId = $handler->id;
        }
        $handler = PropertyHandler::findOne(['handler_class_name' => 'app\properties\handlers\select\SelectProperty']);
        if (!is_null($handler)) {
            $this->selectHandlerId = $handler->id;
        }
        $this->insert(
            PropertyGroup::tableName(),
            [
                'object_id' => $productObject->id,
                'name' => 'Общая группа свойств',
            ]
        );
        $commonGroupId = $this->db->lastInsertID;
        $this->insert(
            Property::tableName(),
            [
                'property_group_id' => $commonGroupId,
                'name' => 'Производитель',
                'key' => 'vendor',
                'property_handler_id' => $this->selectHandlerId,
                'handler_additional_params' => '{}',
                'has_static_values' => 1,
                'has_slugs_in_values' => 1,
            ]
        );
        $this->properties['vendor'] = $this->db->lastInsertID;
        $staticProperties = [
            'Тип крепления бура',
            'Макс. энергия удара',
            'Количество скоростей работы',
            'Питание',
            'Тип процессора',
            'Тип памяти',
            'Частота памяти: 1600 МГц',
            'Количество слотов памяти',
            'Максимальный размер памяти',
            'Размер экрана: 15.6 "',
            'Тип экрана',
            'Тип видеоадаптера',
        ];


        foreach ($data as $category) {
            $this->insert(
                Category::tableName(),
                [
                    'category_group_id' => 1,
                    'parent_id' => 1,
                    'name' => $category['name'],
                    'h1' => $category['name'],
                    'title' => $category['name'] . ' с доставкой в любой город России и СНГ',
                    'breadcrumbs_label' => $category['name'],
                    'slug' => Helper::createSlug($category['name']),
                    'announce' => $category['content'],
                    'content' => $category['content'],
                ]
            );
            $categoryId = $this->db->lastInsertID;
            $this->insert(
                PropertyGroup::tableName(),
                [
                    'object_id' => $productObject->id,
                    'name' => $category['name'],
                ]
            );
            $groupId = $this->db->lastInsertID;
            foreach ($category['products'] as $product) {
                // product
                $slug = Helper::createSlug($product['name']);
                if (isset($this->products[$slug])) {
                    $slug = mb_substr($slug, 0, 66) . '-' . uniqid();
                }
                $this->insert(
                    Product::tableName(),
                    [
                        'parent_id' => 0,
                        'measure_id' => 1,
                        'currency_id' => 1,
                        'sku' => $product['id'],
                        'main_category_id' => $categoryId,
                        'name' => $product['name'],
                        'title' => $product['name'],
                        'breadcrumbs_label' => $product['name'],
                        'h1' => $product['name'],
                        'slug' => $slug,
                        'announce' => Helper::trimPlain($product['description']),
                        'content' => $product['description'],
                        'price' => $product['prices']['min'],
                        'old_price' => $product['prices']['max'],
                    ]
                );
                $productId = $this->db->lastInsertID;
                $this->products[$slug] = $productId;
                // categories
                $this->batchInsert(
                    '{{%product_category}}',
                    ['category_id', 'object_model_id'],
                    [
                        [1, $productId],
                        [$categoryId, $productId],
                    ]
                );
                // property groups
                $this->batchInsert(
                    ObjectPropertyGroup::tableName(),
                    ['object_id', 'object_model_id', 'property_group_id'],
                    [
                        [$productObject->id, $productId, $commonGroupId],
                        [$productObject->id, $productId, $groupId],
                    ]
                );

                // properties
                if (isset($product['vendor'])) {
                    $this->saveStatic($productId, 'vendor', $product['vendor']);
                }
                foreach ($product['details']['modelDetails'] as $group) {
                    foreach ($group['params'] as $property) {
                        $property['name'] = trim($property['name'], '/ ');
                        if (in_array($property['name'], $staticProperties)) {
                            $key = $this->getKey($property['name']);
                            if (!isset($this->properties[$key])) {
                                $this->insert(
                                    Property::tableName(),
                                    [
                                        'property_group_id' => $groupId,
                                        'name' => $property['name'],
                                        'key' => $key,
                                        'property_handler_id' => $this->selectHandlerId,
                                        'handler_additional_params' => '{}',
                                        'has_static_values' => 1,
                                        'has_slugs_in_values' => 1,
                                    ]
                                );
                                $this->properties[$key] = $this->db->lastInsertID;
                            }
                            $this->saveStatic(
                                $productId,
                                $this->getKey($property['name']),
                                str_replace($property['name'] . ': ', '', $property['value'])
                            );
                        } else {
                            $this->saveEav(
                                $productId,
                                $groupId,
                                $property['name'],
                                str_replace($property['name'] . ': ', '', $property['value'])
                            );
                        }
                    }
                }
                // images
                $prodPhotos = [];
                if (isset($product['photos'])) {
                    foreach ($product['photos'] as $photo) {
                        $prodPhotos[] = [
                            $productObject->id,
                            $productId,
                            $photo['name'],
                            $product['name'],
                            $product['name'],
                        ];
                    }
                }
                if (isset($product['mainPhoto']['name'])) {
                    $prodPhotos[] = [
                        $productObject->id,
                        $productId,
                        $product['mainPhoto']['name'],
                        $product['name'],
                        $product['name'],
                    ];
                }
                if (count($prodPhotos) > 0) {
                    $this->batchInsert(
                        Image::tableName(),
                        [
                            'object_id',
                            'object_model_id',
                            'filename',
                            'image_alt',
                            'image_title',
                        ],
                        $prodPhotos
                    );
                }
            }
        }
        srand();
        $cdnNumber = rand(1, 2);
        $imgUrl = "http://static-{$cdnNumber}.dotplant.ru/demo-photos.zip";
        $imagesPath = Yii::getAlias('@webroot/files/');
        $imgsFile = $imagesPath.DIRECTORY_SEPARATOR .'imgs.zip';
        if (file_exists($imgsFile) === false) {
            $fp = fopen($imgsFile, 'w+');
            $ch = curl_init($imgUrl);
            curl_setopt($ch, CURLOPT_TIMEOUT, 600);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_exec($ch);
            curl_close($ch);
            fclose($fp);
        }
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            echo "\n\nWow! You are running windows! Please unzip $imgsFile to $imagesPath \n\n";
        } else {
            passthru('/usr/bin/env unzip -n "' . $imgsFile . '" -d "' . $imagesPath . '"');
        }

    }

    public function down()
    {
        echo "Sorry...\n";
        return false;
    }
}
