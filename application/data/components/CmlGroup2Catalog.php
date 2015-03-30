<?php

namespace app\data\components;

use app\data\models\OnecId;
use Yii;

class CmlGroup2Catalog extends Component {
    const NODE_ID = 'Ид';
    const NODE_GROUP = 'Группа';
    const NODE_GROUPS = 'Группы';
    const NODE_GROUP_NAME = 'Наименование';
    private $data = array ();
    private $canParse = true;
    private $keys = array (
            static::NODE_GROUP_NAME => 'name' 
    );
    public function getData() {
        return $this->data;
    }
    public function getGroups($xml, $name) {
        if (static::NODE_GROUPS === $name) {
            $this->tree = $this->getCatalog ( $xml, $name );
            $this->canParse = false;
            return;
        }
        while ( $xml->read () && true === $this->canParse ) {
            switch ($xml->nodeType) {
                case XMLReader::END_ELEMENT :
                    return;
                    break;
                case XMLReader::ELEMENT :
                    if (! $xml->isEmptyElement) {
                        $this->getGroups ( $xml, $xml->name );
                    }
                    break;
            }
        }
        return;
    }
    private function getCatalog($xml, $name, &$p_node = array()) {
        while ( $xml->read () ) {
            switch ($xml->nodeType) {
                case XMLReader::END_ELEMENT :
                    return;
                    break;
                case XMLReader::ELEMENT :
                    $node = array ();
                    if (static::NODE_GROUPS === $xml->name) {
                        $node ['parent_id'] = isset ( $p_node ['internal_id'] ) ? intval ( $p_node ['internal_id'] ) : 0;
                    }
                    if (static::NODE_GROUP === $xml->name) {
                        $node ['parent_id'] = isset ( $p_node ['parent_id'] ) ? intval ( $p_node ['parent_id'] ) : 0;
                    }
                    $node ['tag'] = $xml->name;
                    if (! $xml->isEmptyElement) {
                        $this->getCatalog ( $xml, $node ['tag'], $node );
                    }
                    if (static::NODE_GROUP === $name && static::NODE_ID === $node ['tag']) {
                        $p_node ['internal_id'] = OnecId::createByGUID ( $node ['text'] )->id;
                    } else {
                        $p_node [$this->getKey ( $node ['tag'] )] = isset ( $node ['text'] ) ? $node ['text'] : '';
                    }
                    if (static::NODE_GROUP === $node ['tag']) {
                        $this->data [] = $node;
                    }
                    break;
                case XMLReader::TEXT :
                case XMLReader::CDATA :
                    $p_node ['text'] = $xml->value;
                    break;
            }
        }
        return;
    }
    private function getKey($key) {
        return isset ( $this->keys [$key] ) ? $this->keys [$key] : $key;
    }
}