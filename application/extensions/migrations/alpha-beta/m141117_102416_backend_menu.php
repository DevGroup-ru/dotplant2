<?php

use yii\db\Schema;
use yii\db\Migration;
use app\backend\models\BackendMenu;

class m141117_102416_backend_menu extends Migration
{
    public function up()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        $this->createTable(
            BackendMenu::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'parent_id' => 'INT UNSIGNED DEFAULT \'0\'',
                'name' => 'VARCHAR(255) NOT NULL',
                'route' => 'VARCHAR(255) NOT NULL',
                'icon' => 'VARCHAR(255) DEFAULT NULL',
                'sort_order' => 'INT UNSIGNED DEFAULT \'0\'',
                'added_by_ext' => 'VARCHAR(255) DEFAULT NULL',
                'rbac_check' => 'VARCHAR(64) DEFAULT NULL',
                'css_class' => 'VARCHAR(255) DEFAULT NULL',
                'translation_category' => 'VARCHAR(120) NOT NULL DEFAULT \'app\'',
                'KEY `ix-backendmenu-parent_id` (`parent_id`)',
            ],
            $tableOptions
        );
        $backend_menu = array(
          array('id' => '1','parent_id' => '0','name' => 'Root','route' => '/backend/','icon' => '','sort_order' => '0','added_by_ext' => 'core','rbac_check' => '','css_class' => '','translation_category' => 'app'),
          array('id' => '2','parent_id' => '1','name' => 'Dashboard','route' => 'backend/dashboard/index','icon' => 'dashboard','sort_order' => '0','added_by_ext' => 'core','rbac_check' => 'administrate','css_class' => '','translation_category' => 'app'),
          array('id' => '3','parent_id' => '1','name' => 'Pages','route' => 'backend/page/index','icon' => 'file-o','sort_order' => '1','added_by_ext' => 'core','rbac_check' => 'content manage','css_class' => '','translation_category' => 'app'),
          array('id' => '4','parent_id' => '1','name' => 'Shop','route' => '','icon' => 'shopping-cart','sort_order' => '2','added_by_ext' => 'core','rbac_check' => 'shop manage','css_class' => '','translation_category' => 'shop'),
          array('id' => '5','parent_id' => '1','name' => 'Properties','route' => '','icon' => 'cogs','sort_order' => '6','added_by_ext' => 'core','rbac_check' => 'property manage','css_class' => '','translation_category' => 'app'),
          array('id' => '6','parent_id' => '1','name' => 'Reviews','route' => 'backend/review/index','icon' => 'comment','sort_order' => '5','added_by_ext' => 'core','rbac_check' => 'review manage','css_class' => '','translation_category' => 'app'),
          array('id' => '7','parent_id' => '1','name' => 'Navigation','route' => 'backend/navigation/index','icon' => 'navicon','sort_order' => '4','added_by_ext' => 'core','rbac_check' => 'navigation manage','css_class' => '','translation_category' => 'app'),
          array('id' => '8','parent_id' => '1','name' => 'Forms','route' => 'backend/form/index','icon' => 'list-ul','sort_order' => '3','added_by_ext' => 'core','rbac_check' => 'form manage','css_class' => '','translation_category' => 'app'),
          array('id' => '9','parent_id' => '1','name' => 'Dynamic content','route' => 'backend/dynamic-content/index','icon' => 'puzzle-piece','sort_order' => '7','added_by_ext' => 'core','rbac_check' => 'content manage','css_class' => '','translation_category' => 'app'),
          array('id' => '10','parent_id' => '1','name' => 'Users','route' => 'backend/user/index','icon' => 'users','sort_order' => '8','added_by_ext' => 'core','rbac_check' => 'user manage','css_class' => '','translation_category' => 'app'),
          array('id' => '11','parent_id' => '1','name' => 'Rbac','route' => 'backend/rbac/index','icon' => 'lock','sort_order' => '9','added_by_ext' => 'core','rbac_check' => 'user manage','css_class' => '','translation_category' => 'app'),
          array('id' => '12','parent_id' => '18','name' => 'Tasks','route' => 'background/manage/index','icon' => 'tasks','sort_order' => '0','added_by_ext' => 'core','rbac_check' => 'task manage','css_class' => '','translation_category' => 'app'),
          array('id' => '13','parent_id' => '1','name' => 'Seo','route' => 'seo/manage/index','icon' => 'search','sort_order' => '10','added_by_ext' => 'core','rbac_check' => 'seo manage','css_class' => '','translation_category' => 'app'),
          array('id' => '14','parent_id' => '18','name' => 'Api','route' => 'backend/api/index','icon' => 'exchange','sort_order' => '15','added_by_ext' => 'core','rbac_check' => 'api manage','css_class' => '','translation_category' => 'app'),
          array('id' => '15','parent_id' => '1','name' => 'Error Monitor','route' => '','icon' => 'flash','sort_order' => '11','added_by_ext' => 'core','rbac_check' => 'monitoring manage','css_class' => '','translation_category' => 'app'),
          array('id' => '16','parent_id' => '1','name' => 'Data','route' => 'data/file/index','icon' => 'database','sort_order' => '12','added_by_ext' => 'core','rbac_check' => 'data manage','css_class' => '','translation_category' => 'app'),
          array('id' => '17','parent_id' => '1','name' => 'Email notify','route' => '','icon' => 'envelope-o','sort_order' => '13','added_by_ext' => 'core','rbac_check' => 'newsletter','css_class' => '','translation_category' => 'app'),
          array('id' => '18','parent_id' => '1','name' => 'Settings','route' => '','icon' => 'gears','sort_order' => '14','added_by_ext' => 'core','rbac_check' => 'setting manage','css_class' => '','translation_category' => 'app'),
          array('id' => '19','parent_id' => '4','name' => 'Categories','route' => 'backend/category/index','icon' => 'tree','sort_order' => '0','added_by_ext' => 'core','rbac_check' => 'category manage','css_class' => '','translation_category' => 'app'),
          array('id' => '20','parent_id' => '4','name' => 'Products','route' => 'backend/product/index','icon' => 'list','sort_order' => '0','added_by_ext' => 'core','rbac_check' => 'product manage','css_class' => '','translation_category' => 'shop'),
          array('id' => '21','parent_id' => '4','name' => 'Orders','route' => 'backend/order/index','icon' => 'list-alt','sort_order' => '0','added_by_ext' => 'core','rbac_check' => 'order manage','css_class' => '','translation_category' => 'shop'),
          array('id' => '22','parent_id' => '4','name' => 'Order statuses','route' => 'backend/order-status/index','icon' => 'info-circle','sort_order' => '0','added_by_ext' => 'core','rbac_check' => 'order status manage','css_class' => '','translation_category' => 'shop'),
          array('id' => '23','parent_id' => '4','name' => 'Payment types','route' => 'backend/payment-type/index','icon' => 'usd','sort_order' => '0','added_by_ext' => 'core','rbac_check' => 'payment manage','css_class' => '','translation_category' => 'shop'),
          array('id' => '24','parent_id' => '4','name' => 'Shipping options','route' => 'backend/shipping-option/index','icon' => 'car','sort_order' => '0','added_by_ext' => 'core','rbac_check' => 'shipping manage','css_class' => '','translation_category' => 'shop'),
          array('id' => '25','parent_id' => '5','name' => 'Properties','route' => 'backend/properties/index','icon' => 'cogs','sort_order' => '0','added_by_ext' => 'core','rbac_check' => 'property manage','css_class' => '','translation_category' => 'app'),
          array('id' => '26','parent_id' => '5','name' => 'Views','route' => 'backend/view/index','icon' => 'desktop','sort_order' => '0','added_by_ext' => 'core','rbac_check' => 'view manage','css_class' => '','translation_category' => 'app'),
          array('id' => '27','parent_id' => '15','name' => 'Monitor','route' => 'backend/error-monitor/index','icon' => 'flash','sort_order' => '0','added_by_ext' => 'core','rbac_check' => 'setting manage','css_class' => '','translation_category' => 'app'),
          array('id' => '28','parent_id' => '15','name' => 'Config','route' => 'backend/error-monitor/config','icon' => 'gear','sort_order' => '0','added_by_ext' => 'core','rbac_check' => 'setting manage','css_class' => '','translation_category' => 'app'),
          array('id' => '31','parent_id' => '17','name' => 'Settings','route' => 'backend/newsletter/config','icon' => 'gears','sort_order' => '3','added_by_ext' => 'core','rbac_check' => 'setting manage','css_class' => '','translation_category' => 'app'),
          array('id' => '32','parent_id' => '17','name' => 'Email list','route' => 'backend/newsletter/email-list','icon' => 'list-alt','sort_order' => '2','added_by_ext' => 'core','rbac_check' => 'newsletter manage','css_class' => '','translation_category' => 'app'),
          array('id' => '33','parent_id' => '17','name' => 'Send now','route' => 'backend/newsletter/newslist','icon' => 'at','sort_order' => '0','added_by_ext' => 'core','rbac_check' => 'newsletter manage','css_class' => '','translation_category' => 'app'),
          array('id' => '34','parent_id' => '18','name' => 'Config','route' => 'backend/config/index','icon' => 'gear','sort_order' => '0','added_by_ext' => 'core','rbac_check' => 'setting manage','css_class' => '','translation_category' => 'app'),
          array('id' => '35','parent_id' => '18','name' => 'I18n','route' => 'backend/i18n/index','icon' => 'language','sort_order' => '0','added_by_ext' => 'core','rbac_check' => 'setting manage','css_class' => '','translation_category' => 'app'),
          array('id' => '36','parent_id' => '18','name' => 'Spam Form Checker','route' => 'backend/spam-checker/index','icon' => 'send-o','sort_order' => '0','added_by_ext' => 'core','rbac_check' => 'setting manage','css_class' => '','translation_category' => 'app'),
          array('id' => '37','parent_id' => '18','name' => 'Backend menu','route' => 'backend/backend-menu/index','icon' => 'list-alt','sort_order' => '0','added_by_ext' => 'core','rbac_check' => 'setting manage','css_class' => '','translation_category' => 'app')
        );
        $this->batchInsert(
            BackendMenu::tableName(),
            array_keys($backend_menu[0]),
            $backend_menu
        );
    }

    public function down()
    {
        $this->dropTable(BackendMenu::tableName());
    }
}
