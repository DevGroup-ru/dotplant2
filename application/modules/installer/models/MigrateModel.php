<?php

namespace app\modules\installer\models;

use Yii;

class MigrateModel extends \yii\base\Model
{
    public $manual_migration_run = false;
    public $composerHomeDirectory = './.composer/';
    public $ignore_time_limit_warning = false;
    public $updateComposer = false;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'manual_migration_run',
                    'ignore_time_limit_warning',
                    'updateComposer',
                ],
                'filter',
                'filter' => 'boolval',
            ],
            [
                [
                    'manual_migration_run',
                    'ignore_time_limit_warning',
                    'updateComposer',
                ],
                'boolean',
            ],
            [
                [
                    'composerHomeDirectory',
                ],
                'required',
            ],
        ];
    }
}