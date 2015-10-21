<?php

namespace app\models;

use app\properties\AbstractModel;
use app\properties\HasProperties;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "submission".
 * @property integer $id
 * @property integer $form_id
 * @property string $date_received
 * @property string $ip
 * @property string $user_agent
 * @property string $piwik_visitor_id
 * @property string $additional_information
 * @property string $date_viewed
 * @property string $date_processed
 * @property integer $processed_by_user_id
 * @property integer $processed
 * @property string $internal_comment
 * @property string $submission_referrer
 * @property string $visitor_referrer
 * @property string $visitor_landing
 * @property string $visit_start_date
 * @property integer $form_fill_time
 * @property integer $is_deleted
 * @property bool $spam
 * @property int $sending_status
 * @property AbstractModel $abstractModel
 * @property Form $form
 */
class Submission extends \yii\db\ActiveRecord
{
    const STATUS_NEW = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_ERROR = -1;
    const STATUS_HOPELESS_ERROR = -2;
    const STATUS_FATAL_ERROR = -3;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%submission}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['form_id'], 'required'],
            [['form_id', 'processed_by_user_id', 'processed', 'form_fill_time', 'is_deleted', 'sending_status'], 'integer'],
            [['date_received', 'date_viewed', 'date_processed', 'visit_start_date'], 'safe'],
            [
                [
                    'ip',
                    'user_agent',
                    'piwik_visitor_id',
                    'additional_information',
                    'internal_comment',
                    'submission_referrer',
                    'visitor_referrer',
                    'visitor_landing'
                ],
                'string'
            ],
            [['spam'], 'integer']
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => HasProperties::className(),
            ],
            [
                'class' => \devgroup\TagDependencyHelper\ActiveRecordHelper::className(),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'form_id' => Yii::t('app', 'Form ID'),
            'date_received' => Yii::t('app', 'Date Received'),
            'ip' => Yii::t('app', 'Ip'),
            'user_agent' => Yii::t('app', 'User Agent'),
            'piwik_visitor_id' => Yii::t('app', 'Piwik Visitor ID'),
            'additional_information' => Yii::t('app', 'Additional Information'),
            'date_viewed' => Yii::t('app', 'Date Viewed'),
            'date_processed' => Yii::t('app', 'Date Processed'),
            'processed_by_user_id' => Yii::t('app', 'Processed By User ID'),
            'processed' => Yii::t('app', 'Processed'),
            'internal_comment' => Yii::t('app', 'Internal Comment'),
            'submission_referrer' => Yii::t('app', 'Submission Referrer'),
            'visitor_referrer' => Yii::t('app', 'Visitor Referrer'),
            'visitor_landing' => Yii::t('app', 'Visitor Landing'),
            'visit_start_date' => Yii::t('app', 'Visit Start Date'),
            'form_fill_time' => Yii::t('app', 'Form Fill Time'),
            'spam' => Yii::t('app', 'Spam Info'),
            'is_deleted' => Yii::t('app', 'Is deleted'),
            'sending_status' => Yii::t('app', 'Sending status'),
        ];
    }

    public function search($params, $form_id = null, $show_deleted = 0)
    {
        /* @var $query \yii\db\ActiveQuery */
        $query = self::find();
        if ($form_id != null) {
            $query->andWhere('form_id = :form_id', [':form_id' => $form_id]);
        }

        $query->andFilterWhere(['is_deleted' => $show_deleted]);

        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'pagination' => [
                    'pageSize' => 10,
                ],
            ]
        );
        if (!($this->load($params))) {
            return $dataProvider;
        }
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['like', 'ip', $this->ip]);
        $query->andFilterWhere(['like', 'user_agent', $this->user_agent]);
        return $dataProvider;
    }

    public function getForm()
    {
        return $this->hasOne(Form::className(), ['id' => 'form_id']);
    }

    public static function getStatuses()
    {
        return [
            self::STATUS_NEW => Yii::t('app', 'New'),
            self::STATUS_SUCCESS => Yii::t('app', 'Success'),
            self::STATUS_ERROR => Yii::t('app', 'Error'),
            self::STATUS_FATAL_ERROR => Yii::t('app', 'Fatal error'),
            self::STATUS_HOPELESS_ERROR => Yii::t('app', 'Hopeless error'),
        ];
    }
}
