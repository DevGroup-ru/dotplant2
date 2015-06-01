<?php

use yii\db\Schema;
use yii\db\Migration;

class m150408_092905_social_refactoring extends Migration
{
    public function up()
    {
        $this->addColumn('{{%user}}', 'username_is_temporary', Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 0');
        $this->execute(
            "UPDATE {{%backend_menu}} SET route=:new_route WHERE route=:old_route",
            [
                ':old_route' => 'backend/user/index',
                ':new_route' => 'user/backend-user/index',
            ]
        );
        $this->execute(
            "UPDATE {{%backend_menu}} SET route=:new_route WHERE route=:old_route",
            [
                ':old_route' => 'backend/rbac/index',
                ':new_route' => 'user/rbac/index',
            ]
        );

    }

    public function down()
    {
        $this->dropColumn('{{%user}}', 'username_is_temporary');

        $this->execute(
            "UPDATE {{%backend_menu}} SET route=:old_route WHERE route=:new_route",
            [
                ':old_route' => 'backend/user/index',
                ':new_route' => 'user/backend-user/index',
            ]
        );
        $this->execute(
            "UPDATE {{%backend_menu}} SET route=:old_route WHERE route=:new_route",
            [
                ':old_route' => 'backend/rbac/index',
                ':new_route' => 'user/rbac/index',
            ]
        );
    }
    
    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }
    
    public function safeDown()
    {
    }
    */
}
