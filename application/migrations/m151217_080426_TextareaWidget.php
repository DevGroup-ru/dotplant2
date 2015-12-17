<?php

use yii\db\Migration;
use app\modules\core\models\Wysiwyg;

class m151217_080426_TextareaWidget extends Migration
{
    public function up()
    {
        $wysiwyg = new Wysiwyg();
        $wysiwyg->class_name = 'app\widgets\TextareaWidget';
        $wysiwyg->configuration_model = '';
        $wysiwyg->configuration_view = '';
        $wysiwyg->name = 'Textarea';
        $wysiwyg->params = json_encode(['width' => 600, 'height' => 400]);
        $wysiwyg->save();
    }

    public function down()
    {
        Wysiwyg::deleteAll(['class_name' => 'app\widgets\TextareaWidget']);
        return true;
    }
}
