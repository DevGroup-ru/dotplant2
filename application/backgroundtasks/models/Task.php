<?php

namespace app\backgroundtasks\models;

use app\backend\models\Notification;
use app\backgroundtasks\traits\SearchModelTrait;
use app\modules\user\models\User;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\helpers\Json;

/**
 * This is the model class for table "backgroundtasks_task".
 *
 * @property integer $id
 * @property string $action
 * @property string $type
 * @property integer $initiator
 * @property string $name
 * @property string $description
 * @property string $params
 * @property string $data
 * @property string $init_event
 * @property string $cron_expression
 * @property string $status
 * @property integer $fail_counter
 * @property string $ts
 * @property string $options
 */
class Task extends ActiveRecord
{
    use SearchModelTrait;

    public $username;

    const MAX_FAIL_COUNT = 5;

    const STATUS_ACTIVE     = 'ACTIVE';
    const STATUS_STOPPED    = 'STOPPED';
    const STATUS_RUNNING    = 'RUNNING';
    const STATUS_PROCESS    = 'PROCESS';
    const STATUS_COMPLETED  = 'COMPLETED';
    const STATUS_FAILED     = 'FAILED';

    const TYPE_EVENT    = 'EVENT';
    const TYPE_REPEAT   = 'REPEAT';

    private $logname = '';
    private $task_options = [];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%backgroundtasks_task}}';
    }

    /**
     * Return array of statuses
     * @param string $type
     * @return array
     */
    public static function getStatuses($type = '')
    {
        switch ($type) {
            case Task::TYPE_EVENT:
                return [
                    self::STATUS_ACTIVE => self::STATUS_ACTIVE,
                    self::STATUS_COMPLETED => self::STATUS_COMPLETED,
                    self::STATUS_FAILED => self::STATUS_FAILED,
                    self::STATUS_RUNNING => self::STATUS_RUNNING,
                ];
            case Task::TYPE_REPEAT:
                return [
                    self::STATUS_ACTIVE => self::STATUS_ACTIVE,
                    self::STATUS_STOPPED => self::STATUS_STOPPED,
                    self::STATUS_FAILED => self::STATUS_FAILED,
                    self::STATUS_RUNNING => self::STATUS_RUNNING,
                    self::STATUS_PROCESS => self::STATUS_PROCESS,
                ];
            default:
                return [
                    self::STATUS_ACTIVE => self::STATUS_ACTIVE,
                    self::STATUS_STOPPED => self::STATUS_STOPPED,
                    self::STATUS_RUNNING => self::STATUS_RUNNING,
                    self::STATUS_PROCESS => self::STATUS_PROCESS,
                    self::STATUS_COMPLETED => self::STATUS_COMPLETED,
                    self::STATUS_FAILED => self::STATUS_FAILED,
                ];
        }
    }

    /**
     * Return array of types
     * @return array
     */
    public static function getTypes()
    {
        return [
            self::TYPE_EVENT => self::TYPE_EVENT,
            self::TYPE_REPEAT => self::TYPE_REPEAT,
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['action', 'initiator', 'name'], 'required', 'except' => 'search'],
            [['cron_expression'], 'required', 'on' => 'repeat'],
            [['init_event'], 'required', 'on' => 'event'],
            [['type', 'description', 'params', 'data', 'status', 'options'], 'string'],
            [['initiator'], 'integer'],
            [['ts', 'username', 'fail_counter'], 'safe'],
            [['action', 'name', 'init_event', 'cron_expression'], 'string', 'max' => 255],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => \Yii::t('app', 'ID'),
            'action' => \Yii::t('app', 'Action'),
            'type' => \Yii::t('app', 'Type'),
            'initiator' => \Yii::t('app', 'Initiator'),
            'name' => \Yii::t('app', 'Name'),
            'description' => \Yii::t('app', 'Description'),
            'params' => \Yii::t('app', 'Parameters'),
            'data' => \Yii::t('app', 'Data'),
            'init_event' => \Yii::t('app', 'Event'),
            'cron_expression' => \Yii::t('app', 'Condition'),
            'ts' => \Yii::t('app', 'Date Modify'),
            'status' => \Yii::t('app', 'Status'),
            'fail_counter' => \Yii::t('app', 'Fail Status Count'),
            'username' => \Yii::t('app', 'Initiator'),
            'options' => \Yii::t('app', 'Options'),
        ];
    }

    /**
     * Return attribute hints
     * @return array
     */
    public function attributeHints()
    {
        return [
            'action' => \Yii::t('app', '[route to the action module/controller/action]'),
            'params' => \Yii::t('app', '[action parameters separated by spaces]'),
        ];
    }

    /**
     * Users relation
     * @return mixed
     */
    public function getUsers()
    {
        return $this->hasMany(User::className(), ['id' => 'user_id'])
            ->viaTable('Notification', ['task_id' => 'id']);
    }

    /**
     * Initiator user relation
     * @return \yii\db\ActiveQueryInterface
     */
    public function getInitiatorUser()
    {
        return $this->hasOne(User::className(), ['id' => 'initiator']);
    }

    /**
     * Set running status
     * @return bool
     */
    public function setRunning()
    {
        return $this->setStatus(self::STATUS_RUNNING);
    }

    /**
     * Set process status
     * @return bool
     */
    public function setProcess()
    {
        return $this->setStatus(self::STATUS_PROCESS);
    }

    /**
     * Set stopped status
     * @return bool
     */
    public function setStopped()
    {
        return $this->setStatus(self::STATUS_STOPPED);
    }

    /**
     * Set completed status
     * @return bool
     */
    public function setCompleted()
    {
        return $this->setStatus(self::STATUS_COMPLETED);
    }

    /**
     * Set active status
     * @return bool
     */
    public function setActive()
    {
        return $this->setStatus(self::STATUS_ACTIVE);
    }

    /**
     * Set failed status
     * @return bool
     */
    public function setFailed()
    {
        return $this->setStatus(self::STATUS_FAILED);
    }

    /**
     * Set status
     * @param $status
     * @return bool
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this->update(false, ['status']);
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->task_options;
    }

    /**
     * @param $options
     */
    public function setOptions($options)
    {
        if (is_array($options)) {
            $this->task_options = $options;
        }
    }

    /**
     * Set failed status counter
     * @return bool
     */
    private function incrementCounter()
    {
        return $this->updateCounters(['fail_counter' => 1]);
    }

    /**
     * Refresh failed status counter
     * @return bool
     */
    private function refreshCounter()
    {
        $this->fail_counter = 0;
        return $this->update(false, ['fail_counter']);
    }

    /**
     * Run task
     */
    public function run()
    {
        $oldts = $this->ts;
        $this->refresh();
        if (($this->status == self::STATUS_ACTIVE || $this->status == self::STATUS_PROCESS) && $this->ts == $oldts) {
            $this->setRunning();
            $message = new NotifyMessage();
            $message->task_id = $this->id;
            \Yii::trace($this->name.' is running', $this->getLogname());
            $args = ' ' . escapeshellarg($this->action);
            /* isJson validate*/
            $params = preg_match('/^{[^}]+}$/iu',$this->params) ? [$this->params]: [$this->params];

            if (!empty($params)) {
                foreach ($params as $param) {
                    $args .= ' ' . escapeshellarg($param);
                }
            }
            exec(\Yii::getAlias('@app/yii') . $args . ' 2>&1', $output, $return_val);
            $message->result = $this->printCommandResult($output);

            if ($return_val === 0) {
                switch ($this->type) {
                    case self::TYPE_EVENT:
                        $this->setCompleted();
                        \Yii::trace($this->name.' completed', $this->getLogname());
                        break;
                    case self::TYPE_REPEAT:
                        $this->setActive();
                        $this->refreshCounter();
                        \Yii::trace($this->name.' completed', $this->getLogname());
                        break;
                    default:
                        $this->setFailed();
                        \Yii::error($this->name.' failed', $this->getLogname());
                        break;
                }
                $message->result_status = NotifyMessage::STATUS_SUCCESS;
            } else {
                switch ($this->type) {
                    case self::TYPE_EVENT:
                        $this->setFailed();
                        \Yii::trace($this->name.' failed', $this->getLogname());
                        break;
                    case self::TYPE_REPEAT:
                        $this->incrementCounter();
                        if ($this->fail_counter >= self::MAX_FAIL_COUNT) {
                            $this->setFailed();
                        } else {
                            $this->setActive();
                        }
                        \Yii::trace($this->name.' failed', $this->getLogname());
                        break;
                    default:
                        $this->setFailed();
                        \Yii::error($this->name.' failed', $this->getLogname());
                        break;
                }
                $message->result_status = NotifyMessage::STATUS_FAULT;
            }
            $message->save();
        }
    }

    /**
     * Logging script output and return string representation
     * @param $output
     * @return string
     */
    private function printCommandResult($output)
    {
        $result = '';
        foreach ($output as $line) {
            \Yii::trace($line, $this->getLogname());
            $result .= "$line\n";
        }
        return strlen($result) > 0 ? $result : '(no message)';
    }

    /**
     * Get log category
     * @return string
     */
    private function getLogname()
    {
        if ($this->logname == '') {
            $this->logname = 'background\task:'
                . ($this->name ? $this->name : 'undefined')
                . '\action:'
                . ($this->action ? $this->action : 'undefined');
        }
        return $this->logname;
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => [
                'id',
                'type',
                'name',
                'description',
                'action',
                'params',
                'initiator',
                'init_event',
                'cron_expression',
                'ts',
                'status'
            ],
            'repeat' => [
                'id',
                'type',
                'name',
                'description',
                'action',
                'params',
                'initiator',
                'cron_expression',
                'ts',
                'status'
            ],
            'event' => [
                'id',
                'type',
                'name',
                'description',
                'action',
                'params',
                'initiator',
                'init_event',
                'ts',
                'status'
            ],
            'search' => [
                'id',
                'name',
                'description',
                'action',
                'params',
                'cron_expression',
                'ts',
                'status',
                'username'
            ],
        ];
    }

    /**
     * Search tasks
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        /* @var $query \yii\db\ActiveQuery */
        $query = self::find();
        $query->joinWith('initiatorUser');

        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'pagination' => [
                    'pageSize' => 10,
                ],
            ]
        );

        $dataProvider->sort->attributes['username'] = [
            'asc' => [User::tableName().'.username' => SORT_ASC],
            'desc' => [User::tableName().'.username' => SORT_DESC],
        ];

        $query->andWhere($this->tableName().'.type = :type', [':type' => 'REPEAT']);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $this->addCondition($query, $this->tableName(), 'id');
        $this->addCondition($query, $this->tableName(), 'name', true);
        $this->addCondition($query, $this->tableName(), 'description', true);
        $this->addCondition($query, $this->tableName(), 'action', true);
        $this->addCondition($query, $this->tableName(), 'params', true);
        $this->addCondition($query, $this->tableName(), 'cron_expression', true);
        $this->addCondition($query, $this->tableName(), 'ts', true);
        $this->addCondition($query, $this->tableName(), 'status');
        $this->addCondition($query, User::tableName(), 'username', true);

        return $dataProvider;
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        $result = parent::beforeSave($insert);

        $this->options = Json::encode($this->task_options);

        return $result;
    }


    /**
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if (empty($changedAttributes)) {
            return ;
        }

        if (!isset($this->task_options['create_notification']) || (false == $this->task_options['create_notification'])) {
            return ;
        }

        if (self::STATUS_ACTIVE === $this->status) {
            Notification::addNotification(
                $this->initiator,
                'Задание "'.$this->description.'" добавлено в очередь.',
                'Задание',
                'info'
            );
        } else if (self::STATUS_RUNNING === $this->status) {
            Notification::addNotification(
                $this->initiator,
                'Задание "'.$this->description.'" выполняется.',
                'Задание',
                'info'
            );
        } else if (self::STATUS_COMPLETED === $this->status) {
            Notification::addNotification(
                $this->initiator,
                'Задание "'.$this->description.'" успешно выполнено.',
                'Задание',
                'success'
            );
        } else if (self::STATUS_FAILED === $this->status) {
            Notification::addNotification(
                $this->initiator,
                'Задание "'.$this->description.'" завершилось с ошибкой.',
                'Задание',
                'danger'
            );
        }
    }

    /**
     *
     */
    public function afterFind()
    {
        parent::afterFind();

        $options = [];
        try {
            $options = Json::decode($this->options);
        } catch(\Exception $e) {
        }

        $this->task_options = $options;
    }
}
