<?php
use yii\helpers\Html;
/**
 * Use existent form
 * @var \yii\web\View $this
 * @var \app\modules\shop\models\Customer $model
 * @var boolean $immutable
 * @var string $action
 * @var \yii\bootstrap\ActiveForm $form
 */
?>
    <table class="table table-striped table-bordered">
        <tbody>
            <tr>
                <th><?= $model->getAttributeLabel('first_name'); ?></th>
                <td><?= Html::encode($model->first_name); ?></td>
            </tr>
            <tr>
                <th><?= $model->getAttributeLabel('middle_name'); ?></th>
                <td><?= Html::encode($model->middle_name); ?></td>
            </tr>
            <tr>
                <th><?= $model->getAttributeLabel('last_name'); ?></th>
                <td><?= Html::encode($model->last_name); ?></td>
            </tr>
            <tr>
                <th><?= $model->getAttributeLabel('email'); ?></th>
                <td><?= Html::encode($model->email); ?></td>
            </tr>
            <tr>
                <th><?= $model->getAttributeLabel('phone'); ?></th>
                <td><?= Html::encode($model->phone); ?></td>
            </tr>
            <?php
                /** @var \app\properties\AbstractModel $abstractModel */
                $abstractModel = $model->getAbstractModel();
                $abstractModel->setArrayMode(false);
                $_tpl = '<tr><th>%s</th><td>%s</td></tr>' . PHP_EOL;
                $_html = '';
                foreach ($abstractModel->attributes() as $attr) {
                    $_html .= sprintf($_tpl, $abstractModel->getAttributeLabel($attr), $abstractModel->getPropertyValueByAttribute($attr));
                }
                echo $_html;
            ?>
        </tbody>
    </table>
