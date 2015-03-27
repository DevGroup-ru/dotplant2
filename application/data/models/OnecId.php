<?php

namespace app\data\models;

use Yii;

/**
 * This is the model class for table "{{%onec_id}}".
 *
 * @property integer $id
 * @property string $onec
 * @property integer $inner_id
 * @property string $entity_id
 */
class OnecId extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%onec_id}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['inner_id'], 'integer'],
            [['onec'], 'string', 'max' => 36],
            [['entity_id'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'onec' => Yii::t('app', '1C'),
            'inner_id' => Yii::t('app', 'Inner ID'),
            'entity_id' => Yii::t('app', 'Entity ID'),
        ];
    }

    /**
     * Get value by GUID
     * @param string $uid
     * @return mixed|null
     */
    public static function getByGUID($guid)
    {
        return self::findOne(['onec' => $guid]);
    }

    
    /**
     * Get value by GUID
     * @param string $uid
     * @return mixed|null
     */
    public static function createByGUID($guid)
    {
    	
    	$obj = self::findOne(['onec' => $guid]);
    	if (null === $obj)
    	{
    		try {
    			$obj = new OnecId();
    			$obj->onec = $guid;
    			$obj->save(false);
    			
    		}
    		catch (Exception $e) {
    			return self::findOne(['onec' => $guid]);
    		}
    	}
    	return $obj;
    }
    
    
    
    
}
