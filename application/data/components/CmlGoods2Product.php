<?php
namespace app\data\components;


use app\data\components\ParserGroupSpecification;
use app\data\models\OnecId;
use Yii;

class CmlGoods2Product extends Component
{

    private $data = array();

    private $canParse = true;

    private $xpath = array();

    private $parserGroupSpecification;

    private $current = array();
    private $groups = array();
    private $information = array();
    private $props = array();
    private $keys = array (
            'Ид' => 'id',
            'Наименование'=>'name'
    );
    
    
    /**
     *
     * @param array $config
     */
    public function __construct ($config = [])
    {
        $this->parserGroupSpecification = new ParserGroupSpecification();
        parent::__construct($config);
    }

    public function getData ()
    {
        if (0===count($this->data))
        {
            $this->parseData();
        }
        return $this->data;
    }

    private function parseData()
    {
        foreach ($this->goods as $values)
        {
            $tmp=array();
            $tmp= array_merge($tmp,$values['good']);
        
            foreach ($values['information'] as $v)
        
            {
                if (isset($v['name']))
                    $tmp= array_merge($tmp,array($v['name']=>$v['value']));
            }
        
            foreach($values['props'] as $v)
            {
                if (count($v['values']) > 0)
                {
                    if (isset($this->specification[$v['id']]))
                    {
                        $tmp= array_merge($tmp,array($this->specification[$v['id']]['Наименование']=>implode('|',$v['values'])));
                    }
                }
            }
            $main_category_id=0;
            $cat = array();
            foreach($values['groups'] as $v)
            {
                if (0===$main_category_id)
                    $main_category_id = OnecId::createByGUID($v)->id;
                else
                    $cat[] = OnecId::createByGUID($v)->id;
            }
            $tmp= array_merge($tmp,array('main_category_id'=>$main_category_id),array('categories'=>implode('|',$cat)));
        
            $k = array_keys($tmp);
            $v = array_values($tmp);
        
            foreach ($k as $key=>$value)
            {
        
                $k[$key] = $this->getKey($value);
            }
        
            $this->data[] = array_combine($k,$v);
        }
        
    }
    public function getProducts ($xml, $name)
    {
        $xpath = implode($this->xpath, '/');
        switch ($xpath) {
            case 'Классификатор/Группы':
                $this->parserGroupSpecification->parse($xml, $name);
                return;
                break;
            case 'Товары':
                $this->getGoods($xml, $name);
                $this->canParse = false;
                return;
                break;
        }
        while ($xml->read() && true === $this->canParse) {
            switch ($xml->nodeType) {
                case XMLReader::END_ELEMENT:
                    switch ($xml->name) {
                        
                        case 'Группы':
                            unset($this->xpath['groups']);
                            break;
                        case 'Товары':
                        case 'Каталог':
                            $this->canParse = false;
                            break;
                        case 'Классификатор':
                            unset($this->xpath['classifier']);
                            break;
                    }
                    return;
                    break;
                case XMLReader::ELEMENT:
                    switch ($xml->name) {
                        case 'Классификатор':
                            $this->xpath['classifier'] = $xml->name;
                            break;
                        case 'Группы':
                            $this->xpath['groups'] = $xml->name;
                            break;
                        case 'Товары':
                            $this->xpath = array(
                                    'goods' => $xml->name
                            );
                            break;
                    }
                    if (! $xml->isEmptyElement) {
                        $this->getProducts($xml, $xml->name);
                    }
                    break;
            }
        }
        return;
    }

    private function getGoods ($xml, $name)
    {
       
        while ($xml->read() && true === $this->canParse) {
            $xpath = implode($this->xpath, '/');
            switch ($xml->nodeType) {
                case XMLReader::END_ELEMENT:
                    
                    if ('Товары' === $xml->name) {
                        $this->canParse = false;
                        return;
                    }
                    switch ($xpath) {
                        case 'Товары/Товар':
                            $this->goods[] = array(
                                    'good' => $this->current,
                                    'groups' => $this->groups,
                                    'information' => $this->information,
                                    'props' => $this->props
                            );
                            break;
                        case 'Товары/Товар/Группы/Ид':
                            $this->groups[] = $node['text'];
                            break;
                        case 'Товары/Товар/ЗначенияРеквизитов/ЗначениеРеквизита':
                            $this->information[] = $this->currentformation;
                            break;
                        case 'Товары/Товар/ЗначенияРеквизитов/ЗначениеРеквизита/Наименование':
                            $this->currentformation['name'] = isset($node['text']) ? $node['text'] : '';
                            break;
                        
                        case 'Товары/Товар/ЗначенияРеквизитов/ЗначениеРеквизита/Значение':
                            $this->currentformation['value'] = isset($node['text']) ? $node['text'] : '';
                            break;
                        case 'Товары/Товар/ЗначенияСвойств/ЗначенияСвойства':
                            $this->props[] = $this->currentprop;
                            break;
                        case 'Товары/Товар/ЗначенияСвойств/ЗначенияСвойства/Ид':
                            $this->currentprop['id'] = isset($node['text']) ? $node['text'] : '';
                            break;
                        case 'Товары/Товар/ЗначенияСвойств/ЗначенияСвойства/Значение':
                            $this->currentprop['values'][] = isset($node['text']) ? $node['text'] : '';
                            break;
                        
                        default:
                            if (3 === count($this->xpath)) {
                                if (isset($node['text']))
                                    $this->current[$xml->name] = $node['text'];
                            }
                            break;
                    }
                    unset($this->xpath[$xml->name]);
                    return;
                    break;
                case XMLReader::ELEMENT:
                    $this->xpath[$xml->name] = $xml->name;
                    
                    $node = array();
                    
                    switch ($xml->name) {
                        case 'Товар':
                            $this->current = array();
                            $this->current = array();
                            $this->groups = array();
                            $this->information = array();
                            $this->props = array();
                            break;
                        case 'ЗначенияСвойства':
                            
                            $this->currentprop = array(
                                    'id' => '',
                                    'values' => array()
                            );
                            break;
                        case 'ЗначениеРеквизита':
                            
                            $this->currentformation = array();
                            break;
                    }
                    
                    $node['tag'] = $xml->name;
                    if (! $xml->isEmptyElement) {
                        $childs = $this->getGoods($xml, $node['tag']);
                        $node['childs'] = $childs;
                    }
                    
                    break;
                case XMLReader::TEXT:
                case XMLReader::CDATA:
                    $node = array();
                    $node['text'] = $xml->value;
                    $tree[] = $node;
                    break;
            }
        }
        return;
    }
    private function getKey($key) {
        return isset ( $this->keys [$key] ) ? $this->keys [$key] : $key;
    }
    
}

