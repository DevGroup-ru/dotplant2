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
        <?=
            array_reduce(OrderStageHelper::getPreviousButtons($stage),
                function ($result, $item)
                {
                    $result .= Html::a($item['label'], $item['url'], ['class' => $item['css']]);
                    return $result;
                }, '');
        ?>
        </div>
        <div class="col-md-5 pull-right">
        <?=
        array_reduce(OrderStageHelper::getNextButtons($stage),
            function ($result, $item)
            {
                $result .= Html::a($item['label'], $item['url'], ['class' => $item['css']]);
                return $result;
            }, '');
        ?>
        </div>
    </div>
</div>