<?php
/** @var array $bindedAddons */
/** @var integer $object_id */
/** @var integer $object_model_id */

use yii\helpers\Html;
use yii\helpers\Url;

?>
<div class="addons-list-widget">
<?php foreach ($bindedAddons as $categoryName => $addons): ?>
    <fieldset>
        <legend><?= Html::encode($categoryName) ?></legend>
        <?php
        $addonsCount = count($addons);
        $index=0;
        $firstAddon = reset($addons);
        if ($firstAddon !== null) {

            $categoryId = $firstAddon->addon_category_id;

            ?>
            <div class="addons-for-category" data-category="<?=$categoryId?>">


                <?php foreach ($addons as $i => $addon): ?>
                    <?php

                    /* @var \app\modules\shop\models\Addon $addon */
                    $index++;
                    ?>
                    <div class="panel panel-default" data-addon="<?=$addon->id?>">
                        <div class="panel-body">
                            <div class="btn btn-xs btn-info pull-left handle" style="margin-right: 20px;cursor: move;">

                                <i class="fa fa-arrows"></i>

                            </div>
                            <div class="pull-right btn-group">
                                <div class="btn btn-xs btn-default">
                                    <?php
                                    $currency = \app\modules\shop\models\Currency::findById($addon->currency_id);
                                    echo $currency->format($addon->price);

                                    ?>

                                </div>
                                <?= Html::a(
                                    \kartik\icons\Icon::show('trash-o') . ' ' . Yii::t('app', 'Delete'),
                                    '#',
                                    [
                                        'data-id' => $addon->id,
                                        'class' => 'btn btn-xs btn-danger remove-addon',
                                    ]
                                ) ?>
                            </div>
                            <?= Html::encode($addon->name) ?>
                        </div>
                    </div>
                <?php endforeach;
                ?>
            </div>
            <?php
        }
            ?>
    </fieldset>
<?php endforeach; ?>

</div>
<?php
$url = \yii\helpers\Json::encode(Url::to(['/shop/backend-addons/add-addon-binding','remove'=>1, 'object_id' => $object_id, 'object_model_id' => $object_model_id]));

$js = <<<JS
$('.addons-widget').on('click', '.remove-addon', function() {
    var id = $(this).data('id');
    $.ajax({
        url: $url,
        data: {
            addon_id: id
        },
        method: 'POST',
        success: function(data) {
            $(".addons-list-widget").replaceWith($(data.data));
        }
    });
    return false;
});

JS;
$this->registerJs($js);
