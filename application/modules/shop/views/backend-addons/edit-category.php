<?php
/**
 * @var \yii\web\View $this
 * @var \app\modules\shop\models\AddonCategory $model
 */

use app\backend\widgets\BackendWidget;
use kartik\form\ActiveForm;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JsExpression;

$this->title = Yii::t('app', 'Addon category edit');
$this->params['breadcrumbs'][] = [
    'url' => 'index',
    'label' => Yii::t('app', 'Addon categories')
];
$this->params['breadcrumbs'][] = $this->title;

?>


<?=app\widgets\Alert::widget(
    [
        'id' => 'alert',
    ]
);?>

<?php $form = ActiveForm::begin(
    ['id' => 'addon-category-form', 'type' => ActiveForm::TYPE_VERTICAL]
); ?>

<?php $this->beginBlock('submit'); ?>
    <?= \app\backend\components\Helper::saveButtons($model) ?>
<?php $this->endBlock(); ?>


<section id="widget-grid">
    <div class="row">

        <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">

            <?php BackendWidget::begin(
                ['title' => Yii::t('app', 'Addon category'), 'icon' => 'tree', 'footer' => $this->blocks['submit']]
            ); ?>
            <?=$form->field($model, 'name')?>

            <?php BackendWidget::end(); ?>

        </article>




    </div>
</section>

<?php ActiveForm::end(); ?>

