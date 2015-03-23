<?php
use kartik\widgets\ActiveForm;
?>
<?php
    if (null !== $form) {
        $_item = \app\models\RatingGroupObject::getOneItemByAttributes(['object_id' => $object_id, 'object_model_id' => $object_model_id]);
        if (empty($_item)) {
            $_item = new \app\models\RatingGroupObject();
        }

        $_list = \app\models\RatingItem::getGroupsAll(true, true);
        $_list = array_column($_list, 'rating_group');
        $_list = [''=>''] + array_combine($_list, $_list);

        echo $form->field($_item, 'rating_group')
            ->dropDownList(
                $_list
            );
    }
?>