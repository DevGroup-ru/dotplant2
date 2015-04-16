<?php

namespace app\modules\user\authclients;

/**
 * Class Facebook is personalized version of base yii2 Facebook client
 * @package app\modules\user\authclients
 */
class Facebook extends \yii\authclient\clients\Facebook {
    /**
     * @inheritdoc
     */
    public $scope = 'public_profile,email';

    /**
     * @inheritdoc
     */
    public function defaultViewOptions()
    {
        return [
            'popupWidth' => 1000,
            'popupHeight' => 600,
        ];
    }
}
