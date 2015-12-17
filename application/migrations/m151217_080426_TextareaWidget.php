<?php

use yii\db\Migration;
use app\modules\core\models\Wysiwyg;

class m151217_080426_TextareaWidget extends Migration
{
    public function up()
    {
        $wysiwyg = new Wysiwyg();
        $wysiwyg->class_name = 'app\widgets\TextareaWidget';
        $wysiwyg->name = 'Textarea';
        $wysiwyg->params = json_encode([
            'htmlOptions' => [
                'style' => 'width: 821px; height: 400px'
            ]
        ]);
        $wysiwyg->save();
    }

    public function down()
    {
        Wysiwyg::deleteAll(['class_name' => 'app\widgets\TextareaWidget']);
        return true;
    }
}
