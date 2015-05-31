<?php
/**
 * @var \app\modules\shop\models\OrderStage $stage
 * @var \yii\web\View $this
 */

use app\modules\shop\helpers\OrderStageHelper;
use yii\helpers\Html;

    $this->title = Html::encode($stage->name_frontend);
?>

<h1><?= Html::encode($stage->name_frontend); ?></h1>

<?php
    $form = \yii\bootstrap\ActiveForm::begin([
        'id' => 'shop-stage',
        'layout' => 'horizontal',
    ]);
    $stageView = Yii::getAlias($stage->view);
    if (is_file($stageView)) {
        $eventData = empty($eventData) ? [] : $eventData;
        echo $this->renderFile($stageView,
            array_merge($eventData, [
                'form' => $form,
            ])
        );
    }
?>
<div class="col-md-12">
    <div class="row">
        <div class="col-md-5">
            <ul class="list-stage-buttons">
            <?=
                array_reduce(OrderStageHelper::getPreviousButtons($stage),
                    function ($result, $item)
                    {
                        $result .= '<li>'.Html::submitButton($item['label'], ['data-action' => $item['url'], 'class' => $item['css']]).'</li>';
                        return $result;
                    }, '');
            ?>
            </ul>
        </div>
        <div class="col-md-5 pull-right">
            <ul class="list-stage-buttons">
            <?=
            array_reduce(OrderStageHelper::getNextButtons($stage),
                function ($result, $item)
                {
                    $result .= '<li>'.Html::submitButton($item['label'], ['data-action' => $item['url'], 'class' => $item['css']]).'</li>';
                    return $result;
                }, '');
            ?>
            </ul>
        </div>
    </div>
</div>
<?php
    $form->end();
    ob_start();
?>
    jQuery(function(){
        $('form#shop-stage button[type="submit"]').on('click', function(event) {
            event.preventDefault();
            $('form#shop-stage').attr('action', $(this).attr('data-action'));
            $('form#shop-stage').submit();
        });
    }(jQuery));
<?php
    $this->registerJs(ob_get_clean(), \yii\web\View::POS_END);
?>

<style>
    .list-stage-buttons li {
        list-style-type: none;
        padding: 5px 0;
    }
</style>
