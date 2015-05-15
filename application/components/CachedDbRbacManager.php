<?php

namespace app\components;

use yii\rbac\DbManager;

class CachedDbRbacManager extends DbManager
{
    private static $assignmentsByUserId = [];

    /**
     * @inheritdoc
     */
    public function getAssignments($userId)
    {
        if (isset(static::$assignmentsByUserId[$userId]) === false) {
            static::$assignmentsByUserId[$userId] = parent::getAssignments($userId);
        }
        return static::$assignmentsByUserId[$userId];
    }

    public function invalidateCache()
    {
        static::$assignmentsByUserId = [];
        return parent::invalidateCache();
    }
}