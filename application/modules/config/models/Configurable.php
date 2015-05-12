<?php

namespace app\modules\config\models;

use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\helpers\Html;

/**
 * This is the model class for table "{{%configurable}}".
 *
 * @property integer $id
 * @property string $module
 * @property integer $sort_order
 * @property string $section_name
 * @property integer $display_in_config
 */
class Configurable extends \yii\db\ActiveRecord
{
    /**
     * @var BaseConfigurationModel Configurable model instance for this Configurable record
     */
    private $configurableModel = null;
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => \devgroup\TagDependencyHelper\ActiveRecordHelper::className(),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%configurable}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['module', 'section_name'], 'required'],
            [['sort_order', 'display_in_config'], 'integer'],
            [['module', 'section_name'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'module' => Yii::t('app', 'Module'),
            'sort_order' => Yii::t('app', 'Sort order'),
            'section_name' => Yii::t('app', 'Section name'),
            'display_in_config' => Yii::t('app', 'Display in config'),
        ];
    }

    /**
     * Returns module configuration view for current Configurable instance
     * @return string
     * @throws InvalidConfigException
     */
    public function getConfigurationView()
    {
        return $this->getModule()->configurationView;
    }

    /**
     * Returns module related to current Configurable instance
     * @return null|\app\components\BaseModule
     * @throws InvalidConfigException
     */
    public function getModule()
    {
        $module = Yii::$app->getModule($this->module);

        if ($module === null) {
            throw new InvalidConfigException(
                Yii::t(
                    'app',
                    'Module {module} not found in application configuration.',
                    [
                        'module' => Html::encode($this->module),
                    ]
                )
            );
        }
        return $module;
    }

    /**
     * Returns module configurable model that will handle user input
     * @return \app\modules\config\models\BaseConfigurationModel
     * @throws InvalidConfigException
     */
    public function getConfigurableModel()
    {
        if ($this->configurableModel === null) {
            $class_name = $this->getModule()->configurableModel;
            $this->configurableModel = new $class_name;
            $this->configurableModel->setModule($this->module);
        }
        return $this->configurableModel;
    }
}
