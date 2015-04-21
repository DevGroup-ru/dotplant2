<?php

namespace app\modules\user\actions;

use app;
use Yii;

/**
 * AuthAction extends base yii2 auth action adding error view
 *
 * @package app\modules\user\actions
 */
class AuthAction extends \yii\authclient\AuthAction
{
    /**
     * @var string View for error displaying. Should include Alert or handle displaying Flash message manually.
     */
    public $errorView = '@app/modules/user/views/user/auth-error';
    /**
     * @inheritdoc
     */
    public function run()
    {
        try {
            return parent::run();
        } catch(\yii\authclient\InvalidResponseException $e) {
            Yii::$app->session->setFlash(
                'warning',
                Yii::t(
                    'app',
                    'Invalid response got: {response}. Please try again.',
                    [
                        'response' => $e->getMessage(),
                    ]
                )
            );
            $this->controller->layout = $this->controller->module->postRegistrationLayout;
            return $this->controller->render(
                $this->errorView
            );
        } catch (\Exception $e) {
            Yii::$app->session->setFlash(
                'error',
                Yii::t(
                    'app',
                    $e->getMessage()
                )
            );
            $this->controller->layout = $this->controller->module->postRegistrationLayout;
            return $this->controller->render(
                $this->errorView
            );
        }
    }
}