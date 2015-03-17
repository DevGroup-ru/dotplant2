<?php

namespace app\backend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "notification".
 *
 * @property string $id
 * @property string $user_id
 * @property string $date
 * @property string $type
 * @property string $label
 * @property string $message
 * @property integer $viewed
 */
class Notification extends ActiveRecord
{
    protected $availableTypes = ['default', 'primary', 'success', 'info', 'warning', 'danger'];

    const STATUS_NOT_VIEWED = 0;
    const STATUS_VIEWED = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%notification}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'label', 'message'], 'required'],
            [['user_id', 'viewed'], 'integer'],
            [['message'], 'string'],
            [['type'], 'in', 'range' => $this->availableTypes],
            [['label'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'date' => Yii::t('app', 'Date'),
            'type' => Yii::t('app', 'Type'),
            'label' => Yii::t('app', 'Label'),
            'message' => Yii::t('app', 'Message'),
            'viewed' => Yii::t('app', 'Viewed'),
        ];
    }

    /**
     * Get allowed types.
     * @return array
     */
    public function getTypesList()
    {
        return $this->availableTypes;
    }

    /**
     * Add new notification.
     * @param integer $user_id
     * @param string $message
     * @param string $label
     * @param string $type
     * @return bool
     */
    public static function addNotification($user_id, $message, $label = 'DotPlant CMS', $type = 'default')
    {
        $notification = new self;
        $notification->attributes = [
            'user_id' => $user_id,
            'message' => $message,
            'label' => $label,
            'type' => $type,
        ];
        return $notification->save();
    }

    /**
     * @param null $user_id
     * @return int|string
     */
    public static function getCountByUserId($user_id = null)
    {
        if (null === $user_id) {
            return 0;
        }

        return static::find()
            ->where([
                'user_id' => intval($user_id),
                'viewed' => 0
            ])
            ->count();
    }

    /**
     * @param null $user_id
     * @param int $viewed
     * @param null $limit
     * @param null $offset
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getAllByUserId($user_id = null, $viewed = self::STATUS_NOT_VIEWED, $limit = null, $offset = null)
    {
        if (null === $user_id) {
            return [];
        }

        $query = static::find()
            ->where(['user_id' => intval($user_id)])
            ->orderBy(['date' => SORT_ASC])
            ->asArray();

        if (null !== $viewed) {
            $query->andWhere(['viewed' => intval($viewed)]);
        }

        if (null !== $limit) {
            $query->limit(intval($limit));

            if (null !== $offset) {
                $query->offset(intval($offset));
            }
        }

        return $query->all();
    }
}
