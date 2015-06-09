<?php

namespace app\extensions\DefaultTheme\models;

use app;
use app\extensions\DefaultTheme\components\StylesCompiler;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;

class ConfigurationModel extends app\models\BaseThemeConfigurationModel
{
    public $primary_color = '#2980b9';
    public $secondary_color = '#27ae60';
    public $action_color = '#d1404a';

    public $logotypePath = 'http://st-3.dotplant.ru/img/sample-logo.png';

    public $logotypeFile = null;

    public $siteName = 'My Awesome DotPlant2 Shop';
    public $primaryEmail = 'noreply@dotplant.ru';

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'primary_color',
                    'secondary_color',
                    'action_color',
                    'logotypePath',
                    'siteName',
                ],
                'string',
            ],
            [
                [
                    'primaryEmail',
                ],
                'email',
            ],
            [
                [
                    'logotypeFile',
                ],
                'file',
                'extensions' => ['png', 'jpg', 'gif'],
            ]
        ];
    }

    /**
     * Fills model attributes with default values
     * @return void
     */
    public function defaultValues()
    {
        /** @var app\extensions\DefaultTheme\Module $module */
        $module = Yii::$app->modules['DefaultTheme'];

        $attributes = array_keys($this->getAttributes(null, ['logotypeFile']));
        foreach ($attributes as $attribute) {
            $this->{$attribute} = $module->{$attribute};
        }
    }

    /**
     * Returns array of module configuration that should be stored in application config.
     * Array should be ready to merge in app config.
     * Used both for web only.
     *
     * @return array
     */
    public function webApplicationAttributes()
    {
        return [
            'modules' => [
                $this->getModule() => ArrayHelper::merge(
                    [
                        'class' => $this->getModuleInstance()->className(),
                    ],
                    $this->getAttributes(null, ['logotypeFile'])
                )
            ]
        ];
    }

    /**
     * Returns array of module configuration that should be stored in application config.
     * Array should be ready to merge in app config.
     * Used both for console only.
     *
     * @return array
     */
    public function consoleApplicationAttributes()
    {
        return [];
    }

    /**
     * Returns array of module configuration that should be stored in application config.
     * Array should be ready to merge in app config.
     * Used both for web and console.
     *
     * @return array
     */
    public function commonApplicationAttributes()
    {
        return [];
    }

    /**
     * Returns array of key=>values for configuration.
     *
     * @return mixed
     */
    public function keyValueAttributes()
    {
        return [];
    }

    /**
     * Returns array of aliases that should be set in common config
     * @return array
     */
    public function aliases()
    {
        if ($this->registerThemeAlias === true) {
            if ($this->getModuleInstance() !== null) {
                $reflectionClass = new \ReflectionClass($this->getModuleInstance());
                return [
                    '@' . $this->getModule() => dirname($reflectionClass->getFileName()),
                ];
            }
        }

        return [];

    }

    /**
     * Override base init function to add event handler that will handle auth clients during configuration editing
     */
    public function init()
    {
        parent::init();
        $this->on(self::configurationSaveEvent(), function($event) {
            /** @var ConfigurationModel $model */
            $model = $event->configurableModel;

            $uploadedFile= UploadedFile::getInstance($model, 'logotypeFile');
            if (is_object($uploadedFile)) {


                if ($model->validate('logotypeFile')) {
                    $fn = $uploadedFile->getBaseName() . $uploadedFile->extension;
                    if ($uploadedFile->saveAs(
                        Yii::getAlias('@app/web/upload/') . $fn
                    )
                    ) {
                        $model->logotypePath = '/upload/' . $fn;
                    }
                }
            }
            //compile theme
            /** @var StylesCompiler $compiler */
            $compiler = Yii::createObject(StylesCompiler::className());

            $compiler->variables($this->getAttributes([
                'primary_color',
                'secondary_color',
                'action_color',
            ]));

            $compiler->compile();

        });
    }
}