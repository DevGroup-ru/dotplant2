<?php
/***
 * @var $object \app\models\BaseObject;
 * @var $model \app\modules\data\models\ImportModel
 */

use app\models\BaseObject;
use \app\modules\shop\models\Product;
?>
<div class="form-group row">
    <div class="col-md-12">
        <fieldset>
            <legend><?= Yii::t('app', 'Filter') ?></legend>
            <?php
            $product = Yii::$container->get(Product::class);
            if ($object->id == BaseObject::getForClass(get_class($product))->id) {
                echo \app\backend\widgets\filterForm\filterFormCategory::widget();
            }
            ?>
            <?= \app\backend\widgets\filterForm\filterFormProperty::widget(['objectId' => $object->id]) ?>
            <?= \app\backend\widgets\filterForm\filterFormFields::widget(['objectId' => $object->id]) ?>
        </fieldset>
    </div>
</div>






