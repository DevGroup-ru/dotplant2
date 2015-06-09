<?php

namespace app\modules\user\authclients;

/**
 * Class GitHub is personalized version of base yii2 github auth client
 * @package app\modules\user\authclients
 */
class GitHub extends \yii\authclient\clients\GitHub {

    /**
     * @inheritdoc
     */
    public $scope = 'user,user:email';

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
