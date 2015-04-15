<?php

namespace app\modules\user\models;

use app;
use app\modules\config\models\BaseConfigurableModel;
use app\validators\ClassnameValidator;
use Yii;

/**
 * Class ConfigConfigurableModel represents configuration model for retrieving user input
 * in backend configuration subsystem.
 *
 * @package app\modules\user\models
 */
class ConfigConfigurableModel extends BaseConfigurableModel
{
    /**
     * Duration of login session for users in seconds.
     * By default 30 days.
     * @var int
     */
    public $loginSessionDuration = 2592000;

    /**
     * Expiration time in seconds for user password reset generated token.
     * @var int
     */
    public $passwordResetTokenExpire = 3600;

    /**
     * @var AuthClientConfig[] Collection of authclients with configuration
     */
    public $authClients = [];

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['passwordResetTokenExpire', 'loginSessionDuration',], 'integer', 'min' => 60],
            [['passwordResetTokenExpire', 'loginSessionDuration',], 'filter', 'filter'=>'intval'],
            [['passwordResetTokenExpire', 'loginSessionDuration',], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function defaultValues()
    {
        $this->loginSessionDuration = Yii::$app->modules['user']->loginSessionDuration;
        $this->passwordResetTokenExpire = Yii::$app->modules['user']->passwordResetTokenExpire;
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
                'user' => [
                    'loginSessionDuration' => $this->loginSessionDuration,
                    'passwordResetTokenExpire' => $this->passwordResetTokenExpire,
                ],
            ],
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
     * Override base init function to add event handler that will handle auth clients during configuration editing
     */
    public function init()
    {
        parent::init();
        Yii::$app->on(self::configurationSaveEvent(), function($event) {
            /** @var ConfigConfigurableModel $model */
            $model = $event->configurableModel;



            if (
                intval(Yii::$app->request->post('addAuthClientFlag', 0)) === 1 &&
                isset($_POST['AuthClientConfig'][-1]['class_name']) === true
            ) {
                // new auth client added
                $class_name = $_POST['AuthClientConfig'][-1]['class_name'];

                $validator = new ClassnameValidator();
                // Hey, there's no error here - validator should return null if there's no errors
                if ($validator->validateValue($class_name) === null) {

                    $new = new AuthClientConfig();
                    $new->class_name = $class_name;
                    $new->determineType();
                    $model->authClients[] = $new;

                } else {
                    Yii::$app->session->addFlash(
                        'error',
                        Yii::t('app', 'The class you wanted to add as auth client doesn\'t exist.')
                    );
                }
            }
            if (
                intval(Yii::$app->request->post('removeAuthClientIndex', -1)) >= 0
            ) {
                $indexToRemove = intval(Yii::$app->request->post('removeAuthClientIndex', -1));
                if (isset($model->authClients[$indexToRemove]) === true) {
                    unset($model->authClients[$indexToRemove]);
                    Yii::$app->session->addFlash(
                        'info',
                        Yii::t('app', 'Auth client was deleted.')
                    );
                } else {
                    Yii::$app->session->addFlash(
                        'error',
                        Yii::t('app', 'Bad auth client index specified.')
                    );
                }
            }

            $authClientsData = Yii::$app->request->post('AuthClientConfig');
            $isValid = true;

            foreach ($authClientsData as $index => $data) {
                if (isset($model->authClients[$index]) === true) {
                    $model->authClients[$index]->setAttributes($data);
                    $model->authClients[$index]->determineType();
                    if ($model->authClients[$index]->validate() === false) {
                        $isValid = false;
                    }
                }
            }



            if ($isValid === false) {
                Yii::$app->session->addFlash(
                    'warning',
                    Yii::t('app', 'Please fill in all required information for auth client configuration.')
                );
            }


        });
    }

    /**
     * @inheritdoc
     */
    public function getAttributesForStateSaving()
    {
        $attributes =  $this->getAttributes(
            null,
            [
                'authClients',
            ]
        );
        $attributes['authClients'] = [];
        foreach ($this->authClients as $index => $client) {
            $attributes['authClients'][$index] = $client->getAttributes();
        }
        return $attributes;
    }

    /**
     * @inheritdoc
     */
    public function loadAttributesFromState($values)
    {
        /** @var array $oldAuthClients */
        $oldAuthClients = isset($values['authClients']) ? $values['authClients'] : [];
        unset($values['authClients']);

        parent::setAttributes($values, false);

        $this->authClients = [];
        foreach ($oldAuthClients as $index => $client) {
            $new = new AuthClientConfig();
            $new->setAttributes($client, false);
            $this->authClients[$index] = $new;
        }

        return true;
    }
}