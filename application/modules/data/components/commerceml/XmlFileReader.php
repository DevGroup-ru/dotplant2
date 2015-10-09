<?php

namespace app\modules\data\components\commerceml;

use app\components\Helper;
use app\modules\data\models\CommercemlGuid;
use app\modules\shop\models\Category;
use app\models\Object;
use app\models\ObjectPropertyGroup;
use app\models\ObjectStaticValues;
use app\modules\shop\models\CategoryGroup;
use app\modules\shop\models\Product;
use app\models\Property;
use app\models\PropertyStaticValues;
use app\properties\AbstractPropertyEavModel;

class XmlFileReader
{
    protected $filename = null;
    /** @var \XMLReader|null $xml */
    protected $xml = null;

    protected $categoryCache = [];
    protected $rootCategoryCache = 1;
    static protected $propertiesCache = [];
    protected $productCache = [];
    /** @var \app\models\Object|null $objectProduct */
    protected $objectProduct = null;

    const NODE_CLASSIFICATOR = 'Классификатор';
    const NODE_CATALOG = 'Каталог';
    const NODE_PAKET_PREDLOZHENIY = 'ПакетПредложений';
    const NODE_SVOISTVA = 'Свойства';
    const NODE_SVOISTVO = 'Свойство';
    const NODE_GRUPPY = 'Группы';
    const NODE_GRUPPA = 'Группа';
    const NODE_TOVARY = 'Товары';
    const NODE_TOVAR = 'Товар';
    const NODE_ZNACHENIYA_SVOISTV = 'ЗначенияСвойств';
    const NODE_PREDLOZHENIYA = 'Предложения';
    const NODE_PREDLOZHENIE = 'Предложение';
    const ELEMENT_ID = 'Ид';
    const ELEMENT_NAIMENOVANIE = 'Наименование';
    const ELEMENT_OPISANIE = 'Описание';
    const ELEMENT_ARTIKUL = 'Артикул';
    const ELEMENT_BAZOVAYA_EDENICA = 'БазоваяЕдиница';
    const ELEMENT_ZNACHENIE_SVOISTVA = 'ЗначенияСвойства';
    const ELEMENT_ZNACHENIE = 'Значение';
    const ELEMENT_KOLICHESTVO = 'Количество';
    const ELEMENT_CENY = 'Цены';
    const ELEMENT_CENA = 'Цена';
    const ELEMENT_CENA_ZA_EDENICU = 'ЦенаЗаЕдиницу';

    const FILETYPE_IMPORT = 1;
    const FILETYPE_OFFERS = 2;

    function __construct($filename)
    {
        if (is_file($filename)) {
            $this->filename = $filename;
            $xml = new \XMLReader();
            if (false !== $xml->open($filename)) {
                $this->xml = $xml;

                $rootCategory = Category::findOne(['parent_id' => 0]);
                if (empty($rootCategory)) {
                    if (null === $rootCategory = Category::createEmptyCategory(0, null, 'Каталог')) {
                        $this->xml->close();
                        $this->xml = null;
                    }
                    $this->rootCategoryCache = $rootCategory->id;
                } else {
                    $this->rootCategoryCache = $rootCategory->id;
                }

                if (empty(static::$propertiesCache)) {
                    static::$propertiesCache = array_reduce(CommercemlGuid::find([['>', 'model_id', 0], ['type' => 'PROPERTY']])->all(),
                        function ($result, $item) {
                            $result[$item['guid']] = $item->property;
                            return $result;
                        }, []);
                }

                $this->objectProduct = Object::getForClass(Product::className());
            }
        }
    }

    /**
     *
     */
    function __destruct()
    {
        if (null !== $this->xml) {
            $this->xml->close();
        }
    }

    /**
     * Try to guess file "structure" for ordering multiple incoming files
     * @return int|null
     */
    public function fileType()
    {
        if (null === $this->xml) {
            return null;
        }
        $xml = $this->xml;

        while ($xml->read()) {
            if (\XMLReader::ELEMENT === $xml->nodeType && 1 === $xml->depth) {
                if (static::NODE_PAKET_PREDLOZHENIY === $xml->name) {
                    return static::FILETYPE_OFFERS;
                } else if (static::NODE_CATALOG === $xml->name) {
                    return static::FILETYPE_IMPORT;
                }
                $xml->next();
            }
        }

        return null;
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        if (null === $this->xml) {
            return [];
        }
        $xml = $this->xml;

        while ($xml->read()) {
            if (\XMLReader::ELEMENT === $xml->nodeType && static::NODE_CLASSIFICATOR === $xml->name) {
                while ($xml->read()) {
                    if (\XMLReader::ELEMENT === $xml->nodeType && static::NODE_SVOISTVA === $xml->name) {
                        return $this->parseSvoistva();
                    }
                }
                break;
            }
        }
    }

    /**
     * @return array
     */
    public function parseSvoistva()
    {
        if (null === $this->xml) {
            return [];
        }
        $xml = $this->xml;

        $result = [];
        while ($xml->read()) {
            if (\XMLReader::END_ELEMENT === $xml->nodeType && static::NODE_SVOISTVA === $xml->name) {
                return $result;
            } else if (\XMLReader::ELEMENT === $xml->nodeType && static::NODE_SVOISTVO === $xml->name) {
                $item = [];
                while ($xml->read()) {
                    if (\XMLReader::END_ELEMENT === $xml->nodeType && static::NODE_SVOISTVO === $xml->name) {
                        if (!empty($item)) {
                            $result[] = $item;
                        }
                        break;
                    } elseif (\XMLReader::ELEMENT === $xml->nodeType && in_array($xml->name, [static::ELEMENT_ID, static::ELEMENT_NAIMENOVANIE])) {
                        $_name = $xml->name;
                        $_value = $this->getElementText($_name);
                        if (!empty($_value)) {
                            $item[$_name] = $_value;
                        }
                    }
                }
            }
        }
    }

    /**
     * @return array|bool
     */
    public function parseProduct()
    {
        if (null === $this->xml) {
            return [];
        }
        $xml = $this->xml;

        while ($xml->read()) {
            if (\XMLReader::END_ELEMENT === $xml->nodeType && static::NODE_TOVARY === $xml->name) {
                return true;
            } elseif (\XMLReader::ELEMENT === $xml->nodeType && static::NODE_TOVAR === $xml->name) {
                $this->createProduct($this->parseTovar());
            }
        }
    }

    /**
     * @return array
     */
    public function parseTovar()
    {
        if (null === $this->xml) {
            return [];
        }
        $xml = $this->xml;
        $depthProduct = $xml->depth;
        $result = [];

        $subElements = [
            static::ELEMENT_ID,
            static::ELEMENT_NAIMENOVANIE,
            static::ELEMENT_ARTIKUL,
            static::ELEMENT_BAZOVAYA_EDENICA,
            static::ELEMENT_OPISANIE,
        ];

        while ($xml->read()) {
            // Достигли окончания Товара
            if (\XMLReader::END_ELEMENT === $xml->nodeType && static::NODE_TOVAR === $xml->name) {
                return $result;
            // Получаем значения для элементов
            } elseif (\XMLReader::ELEMENT === $xml->nodeType && in_array($xml->name, $subElements) && (1 === ($xml->depth - $depthProduct))) {
                $_name = $xml->name;
                $_value = $this->getElementText($_name);
                if (!empty($_value)) {
                    $result[$_name] = $_value;
                }
            // Достигли начала блока Свойств
            } elseif(\XMLReader::ELEMENT === $xml->nodeType && static::NODE_ZNACHENIYA_SVOISTV === $xml->name) {
                $result['properties'] = [];
            // Получаем значения для Свойства
            } elseif (\XMLReader::ELEMENT === $xml->nodeType && static::ELEMENT_ZNACHENIE_SVOISTVA === $xml->name) {
                $_temp = [];
                while ($xml->read()) {
                    if (\XMLReader::END_ELEMENT === $xml->nodeType && static::ELEMENT_ZNACHENIE_SVOISTVA === $xml->name) {
                        if (isset($_temp[static::ELEMENT_ID]) && isset($_temp[static::ELEMENT_ZNACHENIE])) {
                            $result['properties'][$_temp[static::ELEMENT_ID]] = $_temp[static::ELEMENT_ZNACHENIE];
                        }
                        break;
                    } elseif (\XMLReader::ELEMENT === $xml->nodeType && in_array($xml->name, [static::ELEMENT_ID, static::ELEMENT_ZNACHENIE])) {
                        $_name = $xml->name;
                        if (null !== $_value = $this->getElementText($_name)) {
                            $_temp[$_name] = $_value;
                        }
                    }
                }
            // Получаем привязки к Категориям
            } elseif(\XMLReader::ELEMENT === $xml->nodeType && static::NODE_GRUPPY === $xml->name) {
                $result['categories'] = [];
                while ($xml->read()) {
                    if (\XMLReader::END_ELEMENT === $xml->nodeType && static::NODE_GRUPPY === $xml->name) {
                        break;
                    } elseif (\XMLReader::ELEMENT === $xml->nodeType && static::ELEMENT_ID === $xml->name) {
                        if (null !== $_value = $this->getElementText($xml->name)) {
                            $result['categories'][] = $_value;
                        }
                    }
                }
            }
        }

        return $result;
    }

    public function parseGruppa($parent)
    {
        if (null === $this->xml) {
            return [];
        }
        $xml = $this->xml;

        $result = [];
        $lastParent = $parent;

        while ($xml->read()) {
            if (\XMLReader::END_ELEMENT === $xml->nodeType && static::NODE_GRUPPY === $xml->name) {
                return $result;
            } elseif (\XMLReader::ELEMENT === $xml->nodeType && static::NODE_GRUPPY === $xml->name) {
                if (!empty($item)) {
                    $result[] = $lastParent = $this->createCategory($item, $parent);
                }
                $result = array_merge($result, $this->parseGruppa($lastParent));
            } elseif (\XMLReader::ELEMENT === $xml->nodeType && static::NODE_GRUPPA === $xml->name) {
                $item = [];
                $lastParent = $parent;
            } elseif (\XMLReader::END_ELEMENT === $xml->nodeType && static::NODE_GRUPPA === $xml->name) {
                if (!empty($item)) {
                    $result[] = $this->createCategory($item, $parent);
                }
            } elseif (\XMLReader::ELEMENT === $xml->nodeType && in_array($xml->name, [static::ELEMENT_ID, static::ELEMENT_NAIMENOVANIE])) {
                $_name = $xml->name;
                if (null !== $_value = $this->getElementText($_name)) {
                    $item[$_name] = $_value;
                }
            }
        }
    }

    public function parseOffers()
    {
        if (null === $this->xml) {
            return [];
        }
        $xml = $this->xml;

        while ($xml->read()) {
            if (\XMLReader::END_ELEMENT === $xml->nodeType && static::NODE_PREDLOZHENIYA === $xml->name) {
                return true;
            } elseif (\XMLReader::ELEMENT === $xml->nodeType && static::NODE_PREDLOZHENIE === $xml->name) {
                $this->createProduct($this->parsePredlozhenie(), false);
            }
        }
    }

    public function parsePredlozhenie()
    {
        if (null === $this->xml) {
            return [];
        }
        $xml = $this->xml;
        $result = [];

        while ($xml->read()) {
            if (\XMLReader::END_ELEMENT === $xml->nodeType && static::NODE_PREDLOZHENIE === $xml->name) {
                break;
            } elseif (\XMLReader::ELEMENT === $xml->nodeType && in_array($xml->name, [static::ELEMENT_ID, static::ELEMENT_KOLICHESTVO, static::ELEMENT_NAIMENOVANIE])) {
                $_name = $xml->name;
                if (null !== $_value = $this->getElementText($_name)) {
                    $result[$_name] = $_value;
                }
            } elseif (\XMLReader::ELEMENT === $xml->nodeType && static::ELEMENT_CENY === $xml->name) {
                $result['price'] = [];
                while ($xml->read()) {
                    if (\XMLReader::END_ELEMENT === $xml->nodeType && static::ELEMENT_CENY === $xml->name) {
                        break;
                    } elseif (\XMLReader::ELEMENT === $xml->nodeType && static::ELEMENT_CENA_ZA_EDENICU === $xml->name) {
                        $_name = $xml->name;
                        if (null !== $_value = $this->getElementText($_name)) {
                            $result['price'][] = $_value;
                        }
                    }
                }
            }
        }

        return $result;
    }

    public function parse()
    {
        if (null === $this->xml) {
            return [];
        }
        $xml = $this->xml;

        $result = [];

        while ($xml->read()) {
            // Разбор Классификатора
            if (\XMLReader::ELEMENT === $xml->nodeType && static::NODE_CLASSIFICATOR === $xml->name) {
                while ($xml->read()) {
                    if (\XMLReader::END_ELEMENT === $xml->nodeType && static::NODE_CLASSIFICATOR === $xml->name) {
                        break;
                    } elseif (\XMLReader::ELEMENT === $xml->nodeType && static::NODE_GRUPPY === $xml->name) {
                        $result['categories'] = $this->parseGruppa($this->rootCategoryCache);
                    }
                }
            // Разбор Каталога
            } elseif (\XMLReader::ELEMENT === $xml->nodeType && static::NODE_CATALOG === $xml->name) {
                while ($xml->read()) {
                    if (\XMLReader::END_ELEMENT === $xml->nodeType && static::NODE_CATALOG === $xml->name) {
                        break;
                    } elseif (\XMLReader::ELEMENT === $xml->nodeType && static::NODE_TOVARY === $xml->name) {
                        $this->parseProduct();
                    }
                }
            // Разбор Предложений
            } elseif (\XMLReader::ELEMENT === $xml->nodeType && static::NODE_PAKET_PREDLOZHENIY === $xml->name) {
                while ($xml->read()) {
                    if (\XMLReader::END_ELEMENT === $xml->nodeType && static::NODE_PAKET_PREDLOZHENIY === $xml->name) {
                        break;
                    } elseif (\XMLReader::ELEMENT === $xml->nodeType && static::NODE_PREDLOZHENIYA === $xml->name) {
                        $this->parseOffers();
                    }
                }
            }
        }
    }

    /**
     * @param array $item
     * @param int $parentId
     * @return int
     */
    protected function createCategory($item, $parentId)
    {
        if (empty($item) || empty($item[static::ELEMENT_ID]) || empty($item[static::ELEMENT_NAIMENOVANIE])) {
            return $parentId;
        }
        $result = $parentId;

        if (isset($this->categoryCache[$item[static::ELEMENT_ID]])) {
            $result = $this->categoryCache[$item[static::ELEMENT_ID]];
        } else {
            $guid = CommercemlGuid::findOne(['guid' => $item[static::ELEMENT_ID]]);

            if (empty($guid)) {
                $guid = new CommercemlGuid();
                    $guid->guid = $item[static::ELEMENT_ID];
                    $guid->name = $item[static::ELEMENT_NAIMENOVANIE];
                    $guid->type = 'CATEGORY';
                    $guid->model_id = 1;

                $category = Category::findOne(['slug' => Helper::createSlug($item[static::ELEMENT_NAIMENOVANIE]), 'parent_id' => $parentId]);
                if (empty($category)) {
                    if (null !== $category = Category::createEmptyCategory($parentId, null, $item[static::ELEMENT_NAIMENOVANIE])) {
                        $guid->model_id = $category->id;
                    }
                } else {
                    $guid->model_id = $category->id;
                }
                $guid->save();
                $guid->refresh();

                $result = $this->categoryCache[$item[static::ELEMENT_ID]] = $guid->model_id;
            } else {
                $result = $this->categoryCache[$item[static::ELEMENT_ID]] = $guid->model_id;
            }
        }

        return $result;
    }

    protected function createProduct($item = [], $createNotExists = true)
    {
        if (empty($item) || !isset($item[static::ELEMENT_ID]) || !isset($item[static::ELEMENT_NAIMENOVANIE])) {
            return false;
        }

        $guid = CommercemlGuid::findOne(['guid' => $item[static::ELEMENT_ID], 'type' => 'PRODUCT']);
        if (empty($guid) && $createNotExists) {
            $category = !empty($item['categories']) ? array_shift($item['categories']) : null;

            $product = new Product();
                $product->name = $product->title = $product->h1 = $item[static::ELEMENT_NAIMENOVANIE];
                $product->slug = Helper::createSlug($product->name);
                $product->main_category_id = isset($this->categoryCache[$category]) ? $this->categoryCache[$category] : $this->rootCategoryCache;
                $product->content = empty($item[static::ELEMENT_OPISANIE]) ? '' : $item[static::ELEMENT_OPISANIE];
            if ($product->validate() && $product->save()) {
                $product->refresh();
                $product->linkToCategory($this->rootCategoryCache);
                $guid = new CommercemlGuid();
                    $guid->guid = $item[static::ELEMENT_ID];
                    $guid->name = $item[static::ELEMENT_NAIMENOVANIE];
                    $guid->model_id = $product->id;
                    $guid->type = 'PRODUCT';
                $guid->save();

                return true;
            }
        }

        if (!empty($guid)) {
            /** @var Product $product */
            $product = isset($product) ? $product : $guid->product;
            if (!empty($product)) {
                $product->price = empty($item['price']) ?: array_shift($item['price']);
                $product->content = empty($item[static::ELEMENT_OPISANIE]) ?: $item[static::ELEMENT_OPISANIE];
                $product->name = empty($item[static::ELEMENT_NAIMENOVANIE]) ?: $item[static::ELEMENT_NAIMENOVANIE];

                if (!empty($item['properties'])) {
                    AbstractPropertyEavModel::setTableName($this->objectProduct->eav_table_name);
                    $product_eav = array_reduce(AbstractPropertyEavModel::findByModelId($product->id),
                        function ($result, $item)
                        {
                            $key = $item->property_group_id . ':' . $item->key;
                            $result[$key] = $item;
                            return $result;
                        }, []);
                    $product_groups = array_reduce(ObjectPropertyGroup::getForModel($product),
                        function ($result, $item)
                        {
                            $result[] = $item->property_group_id;
                            return $result;
                        }, []);
                    $product_osv = array_reduce(ObjectStaticValues::findAll(['object_id' => $this->objectProduct->id, 'object_model_id' => $product->id]),
                        function ($result, $item)
                        {
                            $result[] = $item->property_static_value_id;
                            return $result;
                        }, []);

                    foreach ($item['properties'] as $key => $value) {
                        if (isset(static::$propertiesCache[$key])) {
                            /** @var Property $prop */
                            $prop = static::$propertiesCache[$key];
                            if (!in_array($prop->property_group_id, $product_groups)) {
                                $objectGroup = new ObjectPropertyGroup();
                                    $objectGroup->object_id = $this->objectProduct->id;
                                    $objectGroup->object_model_id = $product->id;
                                    $objectGroup->property_group_id = $prop->property_group_id;
                                $objectGroup->save();
                            }
                            if ($prop->has_static_values) {
                                $psv = PropertyStaticValues::findOne(['value' => $value]);
                                if (empty($psv)) {
                                    $psv = new PropertyStaticValues();
                                        $psv->name = $psv->value = $value;
                                        $psv->property_id = $prop->id;
                                        $psv->slug = Helper::createSlug($value);
                                        if ($psv->validate() && $psv->save()) {
                                            $psv->refresh();
                                        } else {
                                            $psv = null;
                                        }
                                }
                                if (!empty($psv) && !in_array($psv->id, $product_osv)) {
                                    $osv = new ObjectStaticValues();
                                        $osv->object_id = $this->objectProduct->id;
                                        $osv->object_model_id = $product->id;
                                        $osv->property_static_value_id = $psv->id;
                                    $osv->save();
                                }
                            } elseif ($prop->is_eav) {
                                $_key = $prop->property_group_id . ':' . $prop->key;
                                if (isset($product_eav[$_key])) {
                                    /** @var AbstractPropertyEavModel $eav */
                                    $eav = $product_eav[$_key];
                                        $eav->value = $value;
                                    $eav->save();
                                } else {
                                    $eav = new AbstractPropertyEavModel();
                                        $eav->object_model_id = $product->id;
                                        $eav->property_group_id = $prop->property_group_id;
                                        $eav->key = $prop->key;
                                        $eav->value = $value;
                                    $eav->save();
                                }
                            }
                        }
                    }
                }

                $product->save();

                return true;
            }
        }

        return false;
    }

    /**
     * @param string|null $elementName
     * @param int $nodeType
     * @param null $defaultValue
     * @return string|null
     */
    protected function getElementText($elementName = null, $nodeType = \XMLReader::TEXT, $defaultValue = null)
    {
        if (empty($elementName)) {
            return $defaultValue;
        }

        $xml = $this->xml;
        if (!$xml->isEmptyElement) {
            while ($xml->read()) {
                if (\XMLReader::END_ELEMENT === $xml->nodeType && $elementName === $xml->name) {
                    break;
                } elseif ($nodeType === $xml->nodeType) {
                    return $xml->value;
                }
            }
        }

        return $defaultValue;
    }
}
?>