<?php
/**
 * @var $fields array
 * @var $types array
 * @var $widgetId string
 * @var $this \yii\web\View
 *
 */
use kartik\helpers\Html;

\app\backend\assets\DataRelationsAsset::register($this);

?>

<table id="<?= $widgetId ?>" class="table">
    <?php foreach ($fields as $field): ?>
        <tr id="data-<?= $field['key'] ?>"
            class="<?= (isset($field['required']) && $field['required']) ? 'required' : '' ?>">
            <td>
                <?= $field['label'] ?>
                <?= (isset($field['required']) && $field['required']) ? '<span class="red">*</span>' : '' ?>
            </td>
            <td>
                <?php echo Html::dropDownList(
                    'data[' . $field['key'] . '][type]',
                    null,
                    \yii\helpers\ArrayHelper::merge(['' => Yii::t('app', 'Select ...')], $types),
                    [
                        'class' => 'form-control select-list',
                        'data-key' => $field['key'],
                    ]
                );
                ?>
            </td>
            <td>
            </td>
        </tr>

    <?php endforeach; ?>
</table>

<?php $this->beginBlock('dataRelationsJs'); ?>
        comparisonData.options = <?= json_encode($options) ?>;
        comparisonData.data = <?= json_encode($data); ?>;
        comparisonData.widgetId = '<?=$widgetId?>';
        comparisonData.init();
<?php $this->endBlock(); ?>

<?php $this->registerJs($this->blocks['dataRelationsJs'], \yii\web\View::POS_READY); ?>


