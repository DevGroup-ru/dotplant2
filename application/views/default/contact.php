<?php

/**
 * @var $breadcrumbs array
 * @var $model \app\models\Page
 * @var $this \yii\web\View
 */

?>
<div class="span5"><?= $model->content ?></div>
<div class="span3">
    <?=
        \app\widgets\form\Form::widget(
            [
                'formId' => 1,
            ]
        )
    ?>
</div>