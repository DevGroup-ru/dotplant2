<?php

namespace app\backgroundtasks;

use app\backend\BackendModule;


/**
 * Class BackgroundTasksModule
 * @package app\backgroundtasks
 * @author evgen-d <flynn068@gmail.com>
 */
class BackgroundTasksModule extends BackendModule
{
    /**
     * @var array|string $notifyPermissions<br />
     * Users with those permissions will receive notification
     */
    public $notifyPermissions = [];
    /**
     * @var array|string
     * Users with those roles can manage tasks
     */
    public $manageRoles = [];

    public $daysToStoreNotify = 28;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'configurableModule' => [
                'class' => 'app\modules\config\behaviors\ConfigurableModuleBehavior',
                'configurationView' => '@app/backgroundtasks/views/configurable/_config',
                'configurableModel' => 'app\backgroundtasks\models\ConfigConfigurationModel',
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (!is_array($this->notifyPermissions)) {
            $this->notifyPermissions = explode(',', $this->notifyPermissions);
            foreach ($this->notifyPermissions as $key => $permission) {
                $this->notifyPermissions[$key] = trim($permission);
            }
        }

        if (!is_array($this->manageRoles)) {
            $this->manageRoles = explode(',', $this->manageRoles);
            foreach ($this->manageRoles as $key => $role) {
                $this->manageRoles[$key] = trim($role);
            }
        }
    }

    /**
     * Check whether the permission assigned to the user
     * @param $permission
     * @return bool
     */
    private function checkGroup($permission)
    {
        $user = \Yii::$app->user;

        if ($permission === '?') {
            if ($user->getIsGuest()) {
                return true;
            }
        } elseif ($permission === '@') {
            if (!$user->getIsGuest()) {
                return true;
            }
        } elseif ($user->can($permission)) {
            return true;
        }

        return false;
    }

    /**
     * Check check whether the user is in the access group
     * @return bool
     */
    public function inGroup()
    {
        if (!is_array($this->notifyPermissions)) {
            $this->notifyPermissions = explode(',', $this->notifyPermissions);
            foreach ($this->notifyPermissions as $key => $permission) {
                $this->notifyPermissions[$key] = trim($permission);
            }
        }

        if (empty($this->notifyPermissions)) {
            return false;
        }

        foreach ($this->notifyPermissions as $permission) {
            if ($this->checkGroup($permission)) {
                return true;
            }
        }

        return false;
    }
}
