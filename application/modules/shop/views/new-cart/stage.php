<?php
/**
 * @var \app\modules\shop\models\OrderStage $stage
 * @var \yii\web\View $this
 */

use app\modules\shop\helpers\OrderStageHelper;
use yii\helpers\Html;
?>

<?php
    $stageView = Yii::getAlias($stage->view);
    if (is_file($stageView)) {
        echo $this->renderFile($stageView);
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
                        $result .= '<li>'.Html::a($item['label'], $item['url'], ['class' => $item['css']]).'</li>';
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
                    $result .= '<li>'.Html::a($item['label'], $item['url'], ['class' => $item['css']]).'</li>';
                    return $result;
                }, '');
            ?>
            </ul>
        </div>
    </div>
</div>

<style>
    .list-stage-buttons li {
        list-style-type: none;
        padding: 5px 0;
    }
</style>