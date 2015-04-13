<?php

namespace app\backgroundtasks\models;

use app\backgroundtasks\traits\SearchModelTrait;
use app\modules\user\models\User;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "backgroundtasks_notify_message".
 *
 * @property integer $id
 * @property integer $task_id
 * @property string $result_status
 * @property string $result
 */
class NotifyMessage extends ActiveRecord
{
    use SearchModelTrait;

    public $name;
    public $username;

    const STATUS_SUCCESS = 'SUCCESS';
    const STATUS_FAULT = 'FAULT';

    /**
    * @inheritdoc
    */
    public static function tableName()
    {
        return '{{%backgroundtasks_notify_message}}';
    }

    public static function getStatuses()
    {
        return [
            self::STATUS_SUCCESS => 'success',
            self::STATUS_FAULT => 'fault',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['task_id', 'result_status', 'result'], 'required', 'except' => 'search'],
            [['task_id'], 'integer'],
            [['result_status', 'result'], 'string'],
            [['name', 'username'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'task_id' => 'Task ID',
            'result_status' => 'Result Status',
            'result' => 'Result',
            'ts' => 'Received at',
            'name' => 'Task',
            'username' => 'Initiator',
        ];
    }

    /**
     * Task relation
     * @return \yii\db\ActiveQueryInterface
     */
    public function getTask()
    {
        return $this->hasOne(Task::className(), ['id' => 'task_id']);
    }

    public function scenarios()
    {
        return [
            'default' => ['id', 'task_id', 'result_status', 'result', 'ts'],
            'search' => ['ts', 'name', 'username', 'result_status'],
        ];
    }

    /**
     * Search tasks
     * @param $params
     * @param bool $withUser
     * @return ActiveDataProvider
     */
    public function search($params, $withUser = false)
    {
        $query = self::find();
        $query->joinWith(['task', 'task.initiatorUser']);

        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'pagination' => [
                    'pageSize' => 10,
                ],
                'sort' => [
                    'defaultOrder' => [
                        'ts' => SORT_DESC,
                    ]
                ],
            ]
        );

        $dataProvider->sort->attributes['name'] = [
            'asc' => [Task::tableName().'.name' => SORT_ASC],
            'desc' => [Task::tableName().'.name' => SORT_DESC],
        ];

        $dataProvider->sort->attributes['username'] = [
            'asc' => [\app\modules\user\models\User::tableName().'.username' => SORT_ASC],
            'desc' => [User::tableName().'.username' => SORT_DESC],
        ];

        if ($withUser) {
            $query->andWhere([Task::tableName().'.initiator' => \Yii::$app->user->id]);
        }

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $this->addCondition($query, User::tableName(), 'username', true);
        $this->addCondition($query, $this->tableName(), 'ts', true);
        $this->addCondition($query, Task::tableName(), 'name', true);
        $this->addCondition($query, $this->tableName(), 'result_status');

        return $dataProvider;
    }
}
