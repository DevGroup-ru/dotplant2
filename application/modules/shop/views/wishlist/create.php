<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use app\modules\shop\models\Wishlist;
use kartik\icons\Icon;

/**
 * @var $this yii\web\View
 */
$wishlist = new Wishlist();
?>
    <div class="wishlist-create">
        <a href='#' class="create-wishlist btn btn-success" rel="nofollow">
            <i class="fa fa-plus"></i>
            <?= Yii::t('app', 'New wishlist') ?>
        </a>
    </div>
    <div class="wishlist-create-block" style="display: none">
        <div class="wishlist-create-block-wrap"
             style="background: #ddd; padding: 5px; border-radius: 5px; margin: 5px;">
            <?php $form = ActiveForm::begin([
                'id' => 'wishlist-create',
                'action' => '/shop/wishlist/create',
            ]);
            echo $form->field($wishlist, 'title', [
                'inputOptions' => [
                    'placeholder' => Yii::t('app', 'Enter title'),
                    'name' => 'title',
                    'class' => 'form-control',
                ]
            ])->label('');
            echo Html::submitButton(Icon::show('check') . Yii::t('app', 'Create'), ['class' => 'btn-create-wishlist btn btn-success']);
            ?>
            <a href='#' class="create-wishlist-close btn btn-danger" rel="nofollow" onclick="">
                <i class="fa fa-close"></i>
                <?= Yii::t('app', 'Cancel') ?>
            </a>
            <?php ActiveForm::end() ?>
        </div>
    </div>
<?php
$js = <<<JS
    $('.create-wishlist').on('click', function(){
        $('.wishlist-create-block').css({'display' : 'block'});
        return false;
    });

    $('.create-wishlist-close').on('click', function(){
        $('.wishlist-create-block').css({'display' : 'none'});
        return false;
    });
JS;
$this->registerJs($js);
