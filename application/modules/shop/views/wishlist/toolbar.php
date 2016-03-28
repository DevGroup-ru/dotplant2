<?php
use app\modules\shop\models\Wishlist;
use yii\helpers\Url;
use yii\helpers\Html;

/**
 * @var $this yii\web\View
 * @var $wishlist app\modules\shop\models\Wishlist
 * @var $wishlists array
 */
?>
<div class="wishlist-toolbar" style="border: #ddd 1px solid; padding: 10px; border-radius: 5px; margin: 5px;">
    <h3 class="wishlist-title" style="float: left; margin: 2px;">
        <?= Html::encode($wishlist->title) ?>
    </h3>
    <a href='#' class="rename-wishlist btn btn-warning" rel="nofollow">
        <i class="fa fa-pencil"></i>
        <?= Yii::t('app', 'Rename') ?>
    </a>
    <a href="<?= Url::toRoute([
        '/shop/wishlist/delete',
        'id' => $wishlist->id,
    ]) ?>" class="btn-delete-wishlist btn btn-danger">
        <i class="fa fa-trash-o"></i>
        <?= Yii::t('app', 'Delete') ?>
    </a>
    <?php if ($wishlist->default) : ?>
        <span class="default">
            <i class="fa fa-star"></i>
            <?= Yii::t('app', 'Default list') ?>
        </span>
    <?php else : ?>
        <a href="<?= Url::toRoute([
            '/shop/wishlist/default',
            'id' => $wishlist->id,
        ]) ?>" class="btn-default-wishlist btn btn-info">
            <i class="fa fa-check"></i>
            <?= Yii::t('app', 'Set default') ?>
        </a>
    <?php endif ?>
    <div class="wishlist-sub-toolbar" style="padding: 10px; background: #ddd; border-radius: 5px; margin: 5px;">
        <?php if (!empty($wishlist->items)) : ?>
            <?php
            $ending = '';
            //if($lang == 'ru'){
            if($wishlist->countItems($wishlist->id)%10 > 1 && $wishlist->countItems($wishlist->id)%10 < 5){
                $ending = 'а';
            } elseif($wishlist->countItems($wishlist->id)%10 == 1){
                $ending = '';
            } else {
                $ending = 'ов';
            }
            //}
            ?>
            <?php
            $string = '';
            foreach ($wishlist->items as $item) {
                $string .= $item->product_id . ',';
            } ?>
            <span class="wishlist-price-content" style="font-size: 125%">
                <span class="wishlist-count"><?= $wishlist->countItems($wishlist->id) ?></span>
                <?= Yii::t('app', 'Item') ?>
                <?= $ending ?>
                <?= Yii::t('app', 'in the amount of') ?>
                <span class="wishlist-price">
                    <?= Wishlist::getTotalPrice($wishlist->id) ?>
                </span>
            </span>
            <a href='#' class="buy-wishlist btn btn-success" rel="nofollow" data-action="add-batch-to-cart" data-id="[<?= substr($string, 0, -1) ?>]">
                <?= Yii::t('app', 'Add to') ?>
                <i class="fa fa-shopping-cart"></i>
            </a>
            <?php if (count($wishlists) > 1) : ?>
                <a href='#' class="btn-move-to-wishlist btn btn-warning" rel="nofollow" data-toggle="modal" data-target="#wishlist-move-<?= $wishlist->id ?>">
                    <i class="fa fa-arrows"></i>
                    <?=Yii::t('app', 'Move')?>
                </a>
            <?php endif ?>
            <a href="<?= Url::toRoute([
                '/shop/wishlist/clear',
                'id' => $wishlist->id,
            ]) ?>" class="remove-group-wishlist btn btn-danger" rel="nofollow" data-action="remove-group-wishlist">
                <?= Yii::t('app', 'Delete') ?>
                <i class="fa fa-trash-o"></i>
            </a>
        <?php endif ?>
    </div>
</div>
<?php
$js = <<<JS
    $('.rename-wishlist').on('click', function(e){
        e.preventDefault();
        $(this).parents().siblings('.wishlist-rename-block').css({'display' : 'block'});
    });

    $('.rename-wishlist-close').on('click', function(e){
        e.preventDefault();
        $(this).parents('.wishlist-rename-block').css({'display' : 'none'});
    });
JS;
$this->registerJs($js);
