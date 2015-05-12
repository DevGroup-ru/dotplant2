<?php

namespace app\components;

use app;
use app\modules\shop\models\UserPreferences;
use Yii;
use yii\base\Application;

class UserPreferencesBootstrap implements \yii\base\BootstrapInterface {
    public function bootstrap($app)
    {
        $app->on(
            Application::EVENT_AFTER_REQUEST,
            function () use ($app) {
                $preferences = UserPreferences::preferences();
                $defaultPreferences = new UserPreferences();
                $defaultPreferences->load([]);
                $defaultPreferences->validate();

                // compare current preferences with default
                // if user hasn't changed anything - don't flood session variable with default preferences

                if (count(array_diff($preferences->getAttributes(), $defaultPreferences->getAttributes())) !== 0) {
                    $app->session->set('UserPreferencesModel', $preferences->getAttributes());
                } else {
                    $app->session->set('UserPreferencesModel', null);
                }
            }
        );

        $app->on(
            Application::EVENT_BEFORE_ACTION,
            function() use ($app) {
                UserPreferences::preferences();
            }
        );
    }
} 