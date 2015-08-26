<?php

use yii\db\Schema;
use yii\db\Migration;
use yii\helpers\Json;
use yii\helpers\Url;

class m150717_123227_selectable_wysiwyg extends Migration
{
    public function up()
    {
        mb_internal_encoding("UTF-8");
        $tableOptions = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB'
            : null;
        $this->createTable('{{%wysiwyg}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'class_name' => $this->string()->notNull(),
            'params' => $this->text(),
            'configuration_model' => $this->string(),
        ], $tableOptions);

        $this->insert('{{%wysiwyg}}', [
            'name' => 'Imperavi',
            'class_name' => 'vova07\imperavi\Widget',
            'params' => Json::encode([
                'settings' => [
                    'replaceDivs' => false,
                    'minHeight' => 200,
                    'paragraphize' => false,
                    'pastePlainText' => true,
                    'buttonSource' => true,
                    'imageManagerJson' => Url::to(['/backend/dashboard/imperavi-images-get']),
                    'plugins' => [
                        'table',
                        'fontsize',
                        'fontfamily',
                        'fontcolor',
                        'video',
                        'imagemanager',
                    ],
                    'replaceStyles' => [],
                    'replaceTags' => [],
                    'deniedTags' => [],
                    'removeEmpty' => [],
                    'imageUpload' => Url::to(['/backend/dashboard/imperavi-image-upload']),
                ],
            ]),
            'configuration_model' => 'app\modules\core\models\WysiwygConfiguration\Imperavi',
        ]);


    }

    public function down()
    {
        $this->dropTable('{{%wysiwyg}}');
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
