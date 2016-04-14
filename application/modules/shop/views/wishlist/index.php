<?php
use app\modules\shop\models\Wishlist;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\bootstrap\Modal;
use kartik\icons\Icon;

/**
 * @var $this yii\web\View
 * @var $wishlists app\modules\shop\models\Wishlist
 */

$this->title = Yii::t('app', 'Wishlist');
?>

    <div id="wishlist-block">
        <h1>
            <?= $this->title ?>
        </h1>
        <?= $this->render('create') ?>
        <div id="block-wishlist" class="block-product-list">
            <div class="row">
                <?php if (empty($wishlists)) : ?>
                    <div class="wishlists-are-empty">
                        <h3 class="wishlists-are-empty-title">
                            <?= Yii::t('app', 'You do not have any wishlist yet') ?>
                        </h3>

                        <p class="wishlists-are-empty-note">
                            <?= Yii::t('app', 'Create a wishlist') ?>
                        </p>
                    </div>
                <?php endif ?>

                <?php foreach ($wishlists as $wishlist) : ?>
                    <?php /** @var Wishlist $wishlist */ ?>
                    <div class="wishlist-item" data-id="<?= $wishlist->id ?>">

                        <?= $this->render('toolbar', [
                            'wishlist' => $wishlist,
                            'wishlists' => $wishlists,
                        ]) ?>
                        <?php Modal::begin([
                            'header' => '<h4>' . Yii::t('app', 'Move selected to:') . '</h4>',
                            'size' => Modal::SIZE_SMALL,
                            'id' => 'wishlist-move-' . $wishlist->id,
                            'class' => 'wishlist-move',

                        ]); ?>
                        <?php ActiveForm::begin([
                            'id' => 'move-wishlist-' . $wishlist->id,
                            'action' => '/shop/wishlist/move',
                        ]);
                        echo Html::hiddenInput('wishlistFrom', $wishlist->id);
                        foreach ($wishlists as $item) {
                            if ($item->id !== $wishlist->id) {
                                echo Html::tag('div',
                                    Html::label(Html::radio('wishlistTo', true, ['value' => $item->id]) . Html::encode($item->title) . '<span>(' . count($item->items) . ')</span>'),
                                    [
                                        'class' => 'form-group',
                                    ]
                                );
                            }
                        }
                        echo Html::submitButton(Icon::show('check') . Yii::t('app', 'Save'), ['class' => 'btn-move-wishlist btn btn-success']) ?>
                        <?php ActiveForm::end() ?>
                        <?php Modal::end(); ?>
                        <div class="wishlist-rename-block" style="display: none">
                            <div class="wishlist-rename-block-wrap"
                                 style="background: #ddd; padding: 5px; border-radius: 5px; margin: 5px;">
                                <?php $form = ActiveForm::begin([
                                    'id' => 'wishlist-rename-' . $wishlist->id,
                                    'action' => '/shop/wishlist/rename',
                                ]);
                                echo Html::hiddenInput('id', $wishlist->id);
                                echo $form->field($wishlist, 'title', [
                                    'inputOptions' => [
                                        'placeholder' => Yii::t('app', 'Enter title'),
                                        'name' => 'title',
                                        'class' => 'form-control',
                                    ]
                                ])->label('');
                                echo Html::submitButton(Icon::show('check') . Yii::t('app', 'Save'), ['class' => 'btn-rename-wishlist btn btn-success']);
                                ?>
                                <a href='#' class="rename-wishlist-close btn btn-danger" rel="nofollow">
                                    <i class="fa fa-close"></i>
                                    <?= Yii::t('app', 'Cancel') ?>
                                </a>
                                <?php ActiveForm::end() ?>
                            </div>
                        </div>
                        <?php if (empty($wishlist->items)) : ?>
                            <div class="wishlist-i-empty">
                                <h3 class="wishlist-i-empty-title">
                                    <?= Yii::t('app', 'Your wishlist is empty') ?>
                                </h3>

                                <p class="wishlist-i-empty-note">
                                    <?= Yii::t('app', 'Add items to your wishlist') ?>
                                </p>
                            </div>
                        <?php endif ?>

                        <div class="wishlist-items clearfix">
                            <?php foreach ($wishlist->items as $item) : ?>
                                <?php
                                $url = Url::to([
                                    '@product',
                                    'model' => $item->product,
                                    'category_group_id' => $item->product->category->category_group_id,
                                ]);

                                echo $this->render('item',
                                    [
                                        'product' => $item->product,
                                        'url' => $url,
                                        'wishlist' => $wishlist,
                                        'item' => $item,
                                    ]
                                ) ?>
                            <?php endforeach ?>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>
        </div>
    </div>
<?php
$js = <<<JS
    $('[data-action="remove-group-wishlist"]').on('click', function(e){
        if ($(this).parents('.wishlist-item').find('[type=checkbox]:checked').length !== 0){
            e.preventDefault();
            $(this).parents('.wishlist-item').find('form[action="/shop/wishlist/move"]')
            .attr({'action' : '/shop/wishlist/remove-group'})
            .submit();
        }
    });
JS;
$this->registerJs($js);

$js = <<<JS
    $(".product-item .product-image,.product-item .product-announce").click(function() {
        var that = $(this),
            parent = null;
        if (that.hasClass('product-image')) {
            parent = that.parent();
        } else {
            parent = that.parent().parent();
        }

        document.location = parent.find('a.product-name').attr('href');
        return false;
    });
JS;
$this->registerJs($js, \app\components\WebView::POS_READY, 'product-item-click');