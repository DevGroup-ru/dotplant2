<?php

use yii\db\Schema;
use yii\db\Migration;

class m141216_111515_slider extends Migration
{
    public function up()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable(
            '{{%slider_handler}}',
            [
                'id' => Schema::TYPE_PK,
                'name' => 'VARCHAR(255) NOT NULL DEFAULT \'\'',
                'slider_widget' => 'VARCHAR(255) NOT NULL DEFAULT \'\'',
                'slider_edit_view_file' => 'VARCHAR(255) NOT NULL DEFAULT \'\'',
                'edit_model' => 'VARCHAR(255) NOT NULL DEFAULT \'\'',
            ],
            $tableOptions
        );

        $this->batchInsert(
            '{{%slider_handler}}',
            [
                'name',
                'slider_widget',
                'slider_edit_view_file',
                'edit_model',
            ],
            [
                [
                    'Bootstrap 3 carousel',
                    'app\slider\sliders\bootstrap3\Bootstrap3CarouselWidget',
                    '@app/slider/sliders/bootstrap3/views/edit',
                    'app\slider\sliders\bootstrap3\models\EditModel',
                ],
                [
                    'Slick',
                    'app\slider\sliders\slick\SlickCarouselWidget',
                    '@app/slider/sliders/slick/views/edit',
                    'app\slider\sliders\slick\models\EditModel',
                ],
            ]
        );

        $this->createTable(
            '{{%slider}}',
            [
                'id' => Schema::TYPE_PK,
                'name' => 'VARCHAR(255) NOT NULL DEFAULT \'\'',
                'slider_handler_id' => 'INT UNSIGNED DEFAULT 0',
                'image_width' => 'INT UNSIGNED DEFAULT 0',
                'image_height' => 'INT UNSIGNED DEFAULT 0',
                'resize_big_images' => 'TINYINT(1) NOT NULL DEFAULT 1',
                'resize_small_images' => 'TINYINT(1) NOT NULL DEFAULT 0',
                'css_class' => 'VARCHAR(255) NOT NULL DEFAULT \'\'',
                'params' => 'LONGTEXT NULL',
                'custom_slider_view_file' => 'VARCHAR(255) NOT NULL DEFAULT \'\'',
                'custom_slide_view_file' => 'VARCHAR(255) NOT NULL DEFAULT \'\'',
            ],
            $tableOptions
        );

        $this->batchInsert(
            '{{%slider}}',
            [
                'name',
                'slider_handler_id',
                'image_width',
                'image_height',
            ],
            [
                ['Example carousel', 1, 1170, 480],
            ]
        );

        $this->createTable(
            '{{%slide}}',
            [
                'id' => Schema::TYPE_PK,
                'slider_id' => 'INT UNSIGNED DEFAULT 0',
                'sort_order' => 'INT UNSIGNED DEFAULT 0',
                'image' => 'VARCHAR(255) NOT NULL DEFAULT \'\'',
                'link' => 'VARCHAR(255) NOT NULL DEFAULT \'\'',
                'custom_view_file' => 'VARCHAR(255) NOT NULL DEFAULT \'\'',
                'css_class' => 'VARCHAR(255) NOT NULL DEFAULT \'\'',
                'active' => 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 1',
            ],
            $tableOptions
        );

        $this->batchInsert(
            '{{%slide}}',
            [
                'slider_id',
                'sort_order',
                'image',
                'link',
            ],
            [
                [1, 1, '/demo/images/carousel/1.png', '#1'],
                [1, 2, '/demo/images/carousel/2.png', '#2'],
                [1, 3, '/demo/images/carousel/3.png', '#3'],
                [1, 4, '/demo/images/carousel/4.png', '#4'],
            ]
        );

    }

    public function down()
    {
        $this->dropTable('{{%slider_handler}}');
        $this->dropTable('{{%slider}}');
        $this->dropTable('{{%slide}}');
    }
}
