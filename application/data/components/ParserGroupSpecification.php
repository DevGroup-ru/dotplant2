<?php
namespace app\data\components;
use Yii;

class ParserGroupSpecification extends Component
{

    const NODE_ID = 'Ид';
    const NODE_PROP = 'Свойство';

    private $current = array();
    private $specification = array();

    public function getSpecification ()
    {
        return $this->specification;
    }

    public function parser ($xml, $name)
    {
        while ($xml->read()) {
            switch ($xml->nodeType) {
                case XMLReader::END_ELEMENT:
                    switch ($xml->name) {
                        case static::NODE_PROP:
                            $this->specification[$this->current[static::NODE_ID]] = $this->current;
                            break;
                        default:
                            if (isset($node['text']))
                                $this->current[$xml->name] = $node['text'];
                            break;
                    }
                    return;
                    break;
                case XMLReader::ELEMENT:
                    $node = array();
                    
                    switch ($xml->name) {
                        case 'Свойство':
                            $this->current = array();
                            break;
                    }
                    $node['tag'] = $xml->name;
                    
                    if (! $xml->isEmptyElement) {
                        $this->parse($xml, $node['tag']);
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
}