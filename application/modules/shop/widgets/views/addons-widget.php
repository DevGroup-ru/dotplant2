<?php
use \kartik\icons\Icon;
use yii\helpers\Html;
use yii\helpers\Url;
use app\backend\widgets\BackendWidget;
use app\modules\shop\widgets\AddonsListWidget;
/**
 * @var \yii\web\View $this
 * @var \app\models\Object $object
 * @var \yii\db\ActiveRecord $model
 * @var array $addAddonTree
 * @var array $bindedAddons
 */
/** @var \app\modules\shop\models\AddonCategory $addonCategories */
/** @var app\backend\components\ActiveForm $form */
/** @var app\modules\shop\models\AddAddonModel $addAddonModel */

?>

<?php BackendWidget::begin(
    ['title' => Yii::t('app', 'Addons'), 'icon' => 'cart-plus', 'footer' => $this->blocks['submit']]
); ?>
<?=
\app\backend\widgets\Select2Ajax::widget([
    'initialData' => [],
    'form' => $form,
    'model' => $addAddonModel,
    'modelAttribute' => 'addon_id',
    'multiple' => false,
    'searchUrl' => \yii\helpers\Url::to(['/shop/backend-addons/ajax-search-addons']),
    'additional' => [
        'placeholder' => Yii::t('app', 'Search addons...'),
    ],

]);
?>

<?= AddonsListWidget::widget([
    'object_id' => $object->id,
    'object_model_id' => $model->id,
    'bindedAddons' => $bindedAddons,
]) ?>

<?php BackendWidget::end() ?>
<?php
$url = \yii\helpers\Json::encode(Url::to(['/shop/backend-addons/add-addon-binding', 'object_id' => $object->id, 'object_model_id' => $model->id]));
$js = <<<JS
    $("#addaddonmodel-addon_id").on('change', function(){
        var val = $(this).val();
        if (val === '' || val === '0') {
            return;
        }
        // here comes the dragon! $(this).val() is the addon_id to be added
        $.ajax({
            url: $url,
            data: {
                addon_id: $(this).val()
            },
            method: 'POST',
            success: function(data) {
                $(".addons-list-widget").replaceWith($(data.data));
            }
        });

    });

JS;
$this->registerJs($js);
