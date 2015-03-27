<?php

namespace app\models;

use Yii;
use app\models\OnecId;

/**
 * This is the model class for table "{{%onec_id}}".
 *
 * @property integer $id
 * @property string $onec
 * @property integer $inner_id
 * @property string $entity_id
 */
class DocumentNode extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%document_nodes}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['nodeType'], 'integer'],
            [['nodeName'], 'string', 'max' => 255],
        	[['namespaceUri'], 'string', 'max' => 255],
        	[['ownerDocument'], 'integer'],
        	[['parentNode'], 'integer'],
        	[['pefix'], 'string', 'max' => 100],
        	[['nextSibling'], 'integer'],
        	[['lft'], 'integer'],
        	[['rgt'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'nodeType' => Yii::t('app', 'Node Type'),
            'nodeName' => Yii::t('app', 'Node Name'),
            'nodeValue' => Yii::t('app', 'Node Value'),
        	'namespaceUri' => Yii::t('app', 'namespaceUri'),
        	'ownerDocument' => Yii::t('app', 'Document'),
        	'parentNode' => Yii::t('app', 'Parent'),
        	'pefix' => Yii::t('app', 'pefix'),
        	'nextSibling' => Yii::t('app', 'Next Sibling'),
        		
        		
        ];
    }

    /**
     * create Root Node by ownerDoc and name
     * @param integer $IdDocument
     * @param string $name
     * @return mixed | null
     */
    public static function createRootNode($IdDocument,$name)
    {
    	$lft = 0;
    	$rgt = 0;
    	 
        $obj = DocumentNode::find()
        				->select(['rgt'])
        				->where(['ownerDocument' => $IdDocument])
        				->orderBy(['rgt'=>SORT_DESC])
        				->one();
        if (null === $obj) {
        	$lft = 1;
        	$rgt = 2;
        }
        else {
        	$lft = $obj->rgt + 1;
        	$rgt = $lft + 1;
        }	
        $newNode = new DocumentNode();
        $newNode->lft = $lft;
        $newNode->rgt = $rgt;
        $newNode->name = $name;
        $newNode->ownerDocument = $IdDocument;
        $newNode->save(false);
        return $newNode;
    }
    /**
     * Creates a new node
     * @param integer $IdDocument
     * @param string $name name of the new node
     * @param integer $lft lft of parent node
     * @param integer $rgt	rgt of parent node
     * @return mixed | null
     */
    private static function insertNode($IdDocument,$name, $id, $lft, $rgt ) {
    	
    	Yii::$app->db->createCommand('UPDATE ' . static::tableName() . ' SET rgt = rgt + 2 WHERE rgt >= ' . $rgt . ' AND ownerDocument =' . $IdDocument)->execute();	
    	Yii::$app->db->createCommand('UPDATE ' . static::tableName() . ' SET lft = lft + 2 WHERE lft > ' . $rgt . ' AND ownerDocument =' . $IdDocument)->execute();

    	$newNode = new DocumentNode();
    	$newNode->ownerDocument = $IdDocument;
    	$newNode->name = $name;
    	$newNode->lft = $rgt;
    	$newNode->rgt = $rgt+1;
    	$newNode->parentNode = $id;
    	$newNode->save(false);
    	
    	return $newNode;
    }

    /**
     * Creates a new child node of the node with the given id
     * @param integer $IdDocument
     * @param string $name name of the new node
     * @param integer $parent id of the parent node
     * @return boolean true
     */
    public static function insertChildNode($IdDocument,$name, $parent) {
    	
    	$p_node =  $model = static::find()->where(['ownerDocument'=>$IdDocument,'id' => $parent])->one();
    	return self::insertNode($IdDocument,$name, $p_node->id, $p_node->lft, $p_node->rgt);
    }
        
    /**
    * Deletes a node an all it's children
    * @param integer $IdDocument
	 * @param integer $id id of the node to delete
	 * @return boolean true
	 */
	public static function deleteNode($IdDocument, $id) {
		
		$node = $model = static::find()->where(['id' => $id]);
		Yii::$app->db->createCommand("DELETE FROM " . static::tableName() . " WHERE lft BETWEEN " . $node->lft . " AND " . $node->rgt . ' AND ownerDocument =' . $IdDocument)->execute();
		Yii::$app->db->createCommand("UPDATE " . $this->table . " SET lft = lft - ROUND((" . $node->rgt . " - " . $node->lft . " + 1)) WHERE lft > " . $node->rgt . ' AND ownerDocument =' . $IdDocument)->execute();
		Yii::$app->db->createCommand("UPDATE " . $this->table . " SET rgt = rgt - ROUND((" . $node->rgt . " - " . $node->lft . " + 1)) WHERE rgt > " . $node->rgt . ' AND ownerDocument =' . $IdDocument)->execute();
		return true;
	}    

	/**
	 * Deletes a node and increases the level of all children by one
	 * @param integer $IdDocument
	 * @param integer $id id of the node to delete
	 * @return boolean true
	 */
	public function deleteSingleNode($IdDocument,$id) {
		$node = $model = static::find()->where(['id' => $id]);
		
		Yii::$app->db->createCommand("DELETE FROM " . $this->table . " WHERE lft = " . $node->lft . ' AND ownerDocument =' . $IdDocument)->execute();
		Yii::$app->db->createCommand("UPDATE " . $this->table . " SET lft = lft - 1, rgt = rgt - 1 WHERE lft BETWEEN " . $node->lft . " AND " . $node->rgt . ' AND ownerDocument =' . $IdDocument)->execute();
		Yii::$app->db->createCommand("UPDATE " . $this->table . " SET lft = lft - 2 WHERE lft > " . $node->rgt . ' AND ownerDocument =' . $IdDocument)->execute();
		Yii::$app->db->createCommand("UPDATE " . $this->table . " SET rgt = rgt - 2 WHERE rgt > " . $node->rgt . ' AND ownerDocument =' . $IdDocument)->execute();
		return true;
	}

	/**
	 * get Node Childs
	 * @param integer $lft
	 * @param integer $rgt
	 * @return array
	 */
	
	
	public static function getNodeChilds($lft,$rgt)
	{
		
		return DocumentNode::find()
        				->select('*')
        				->where(['>=','lft',$lft])
        				->andWhere(['<=','rgt',$rgt])
        				->orderBy(['lft'=>SORT_ASC])
        				->asArray()->all();
	}

	public static function getParentGroupId($lft,$rgt)
	{
		$obj = DocumentNode::find()
				->where(['<','lft',$lft])
				->andWhere(['>=','rgt',$rgt])
				->andWhere(['=','name','Группа'])
				->orderBy(['lft'=>SORT_DESC])->one();
		
		if (null == $obj) return 0;
		
		$obj1 = DocumentNode::find()
				->select('nodeValue')
				->where(['parentNode'=>
				$obj->id,'name'=>'Ид'
		])->one();
		
		if (null ==$obj1) 
			return 0;
		
		return OnecId::getByGUID($obj1->nodeValue)->id;	
	}
	
	
	
	public static function getFirstCatalogInDocument($IdDocument)
	{
		
		return self::find()
		->where(
				[
						'ownerDocument' => $IdDocument,
						'name'=>'Группы'
				]
		)
		->orderBy(['lft'=>SORT_ASC])
		->one();
		
		
	}
	
}
