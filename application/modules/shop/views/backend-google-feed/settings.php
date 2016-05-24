<?php
/**
 * @var $this \yii\web\View
 * @var $model \app\modules\shop\models\Yml
 **/
use \yii\helpers\Html;
use \kartik\icons\Icon;
use \app\backend\widgets\BackendWidget;
use yii\helpers\ArrayHelper;

$this->title = Yii::t('app', 'Settings');
$this->params['breadcrumbs'][] = 'Google Feed';

\app\backend\assets\YmlAsset::register($this);
$formName = 'YmlSettings';

$currencies = ArrayHelper::map(
    \app\modules\shop\models\Currency::find()
        ->select('iso_code')
        ->orderBy(['is_main' => SORT_DESC, 'sort_order' => SORT_ASC, 'iso_code' => SORT_ASC])
        ->asArray()
        ->all(),
    'iso_code',
    'iso_code'
);
?>

<?= app\widgets\Alert::widget([
    'id' => 'alert',
]); ?>

<?php
$feed_relations = [
    'getImage' => \app\modules\image\models\Image::className(),
    'getMainCategory' => \app\modules\shop\models\Category::className(),
];

$feed_settings = [];
$feed_settings['product_fields'] = (new \app\modules\shop\models\Product())->attributeLabels();
$feed_settings['relations_keys'] = array_combine(array_keys($feed_relations), array_keys($feed_relations));
$feed_settings['relations_map'] = [];
foreach ($feed_relations as $key => $value) {
    $_fields = (new $value)->attributeLabels();
    $feed_settings['relations_map'][$key] = [
        'fields' => $_fields,
        'html' => array_reduce($_fields, function ($result, $i) {
            $result .= '<option value="' . addslashes(htmlspecialchars($i)) . '">' . addslashes(htmlspecialchars($i)) . '</option>';
            return $result;
        }, ''),
    ];
}
if (true === isset ($feed_settings['relations_map']['getImage'])) {
    $feed_settings['relations_map']['getImage']['fields']['file'] = Yii::t('app', 'File');
    $feed_settings['relations_map']['getImage']['html'] .= '<option value="file">' . Yii::t('app',
            'File') . '</option>';
}
$prop_group = \app\models\Object::getForClass(\app\modules\shop\models\Product::className())->id;
$provider = (new \yii\db\Query())
    ->select(['pg.name as pgname', 'p.name', 'p.id', 'p.handler_additional_params'])
    ->from(\app\models\Property::tableName() . ' as p', \app\models\PropertyGroup::tableName() . 'as pg')
    ->leftJoin(\app\models\PropertyGroup::tableName() . 'as pg', 'pg.id=p.property_group_id')
    ->where(['pg.object_id' => $prop_group]);
$prop_group = $provider->all();
$feed_settings['properties_map'] = ['html' => '', 'fields' => []];
$feed_settings['properties_map'] = array_reduce(
    array_reduce(
        $prop_group,
        function ($result, $i) {
            if (isset($result[$i['pgname']])) {
                $result[$i['pgname']]['html'] .= '<option value="' . addslashes(htmlspecialchars($i['id'])) . '">' . addslashes(htmlspecialchars($i['name'])) . '</option>';
                $result[$i['pgname']]['fields'][$i['pgname']][$i['id']] = $i['name'];
                $result[$i['pgname']]['name'] = $i['pgname'];
            }
            return $result;
        },
        array_fill_keys(array_unique(array_column($prop_group, 'pgname')), ['fields' => [], 'html' => '', 'name' => ''])
    ),
    function ($result, $i) {
        $result['fields'] = array_replace($result['fields'], $i['fields']);
        $result['html'] .= '<optgroup label="' . addslashes(addslashes($i['name'])) . '">' . addslashes(addslashes($i['html'])) . '</optgroup>';
        return $result;
    },
    $feed_settings['properties_map']
);
?>

<?php $form = \kartik\widgets\ActiveForm::begin([
    'id' => 'yml-form',
    'type' => \kartik\widgets\ActiveForm::TYPE_HORIZONTAL
]); ?>
<div class="row">
    <div class="col-md-6">
        <?php BackendWidget::begin(['title' => Yii::t('app', 'Settings for shop section'), 'icon' => 'cogs']); ?>
        <?= $form->field($model, 'shop_host') ?>
        <?= $form->field($model, 'shop_name') ?>
        <?= $form->field($model, 'shop_description')->textarea(); ?>
        <?= $form->field($model, 'feed_handlers')->widget(\devgroup\jsoneditor\Jsoneditor::className()); ?>
        <?= $form->field($model, 'feed_file_name') ?>


        <?= Html::submitButton(
            Icon::show('save') . Yii::t('app', 'Save'),
            ['class' => 'btn btn-primary']
        ); ?>
        <?= Html::a(
            Icon::show('code') . Yii::t('app', 'Create Google feed'),
            ['create'],
            ['class' => 'btn btn-primary']
        ); ?>

        <?php
        if (is_file(\Yii::getAlias('@webroot') . '/' . $model->feed_file_name)) {
            echo Html::a(
                Icon::show('download') . Yii::t('app', 'Download'),
                \yii\helpers\Url::to($model->feed_file_name, true),
                ['class' => 'btn btn-default']
            );
        }
        ?>
        <?php BackendWidget::end(); ?>
    </div>

    <div id="offers_section" class="col-md-6">
        <?php BackendWidget::begin(['title' => Yii::t('app', 'Settings for currencies section'), 'icon' => 'cogs']); ?>
        <?= $form->field($model, 'shop_main_currency')->dropDownList($currencies); ?>
        <?php BackendWidget::end(); ?>

        <?php BackendWidget::begin(['title' => Yii::t('app', 'Settings for offers section'), 'icon' => 'cogs']); ?>

        <?= $form->field($model, 'item_condition')->dropDownList([
            'new'=>'New',
            'used' => 'Used'

        ]); ?>

        <?php
        foreach ($model->getOfferElements() as $el) {
            echo $form->field($model, $el)->render(
                function (\yii\widgets\ActiveField $field) use ($feed_settings) {
                    $value = $field->model->{$field->attribute};

                    $label = Html::activeLabel($field->model, $field->attribute, $field->labelOptions);

                    $input = '<div class="col-md-4">' .
                        Html::activeDropDownList(
                            $field->model,
                            $field->attribute . '[type]',
                            [
                                'field' => Yii::t('app', 'Field'),
                                'property' => Yii::t('app', 'Property'),
                                'relation' => Yii::t('app', 'Relation'),
                            ],
                            array_merge(['data-ymlselect' => 'type'], $field->inputOptions)
                        ) . '</div>';

                    $_key_list = [];
                    if (!empty($value['key'])) {
                        if ('field' === $value['type']) {
                            $_key_list = $feed_settings['product_fields'];
                        } elseif ('property' === $value['type']) {
                            $_key_list = $feed_settings['properties_map']['fields'];
                        } elseif ('relation' === $value['type']) {
                            $_key_list = $feed_settings['relations_keys'];
                        }
                    }

                    $input .= '<div class="col-md-6">' .
                        Html::activeDropDownList(
                            $field->model,
                            $field->attribute . '[key]',
                            $_key_list,
                            array_merge(['data-ymlselect' => 'key'], $field->inputOptions)
                        ) . '</div>';

                    $_value_list = [];
                    if (!empty($value['value']) && 'relation' === $value['type']) {
                        $_value_list = $feed_settings['relations_map'][$value['key']]['fields'];
                    }
                    $input .= '<div class="col-md-6">' .
                        Html::activeDropDownList(
                            $field->model,
                            $field->attribute . '[value]',
                            $_value_list,
                            array_merge([
                                'data-ymlselect' => 'value',
                                'style' => empty($_value_list) ? 'display:none;' : ''
                            ], $field->inputOptions)
                        ) . '</div>';

                    $error = Html::error($field->model, $field->attribute, $field->errorOptions);
                    return sprintf("%s\n<div class=\"col-md-10 offer-group\">%s</div>\n<div class=\"col-md-offset-2 col-md-10\">%s</div>",
                        $label, $input, $error);
                }
            );
        }
        ?>

        <?php BackendWidget::end(); ?>
    </div>
</div>


<?php \kartik\widgets\ActiveForm::end(); ?>

<?php $this->beginBlock('jsValues') ?>
var $url = '<?= \yii\helpers\Url::toRoute(['/shop/backend-yml/save-property-unit']) ?>';
var ymlSelectFields = '<?= array_reduce(
    $feed_settings['product_fields'],
    function ($result, $i) {
        $result .= '<option value="' . addslashes(htmlspecialchars($i)) . '">' . addslashes(htmlspecialchars($i)) . '</option>';
        return $result;
    }, ''); ?>';
var ymlSelectRelKeys = '<?= array_reduce(
    $feed_settings['relations_keys'],
    function ($result, $i) {
        $result .= '<option value="' . addslashes(htmlspecialchars($i)) . '">' . addslashes(htmlspecialchars($i)) . '</option>';
        return $result;
    }, ''); ?>';
var ymlSelectRelations = {
<?php
foreach ($feed_settings['relations_map'] as $k => $v) {
    echo $k . ': \'' . $v['html'] . '\',' . PHP_EOL;
}
?>
};
var ymlSelectProperties = '<?= $feed_settings['properties_map']['html']; ?>';
<?php $this->endBlock(); ?>

<?php $this->registerJs($this->blocks['jsValues'], \yii\web\View::POS_HEAD); ?>
