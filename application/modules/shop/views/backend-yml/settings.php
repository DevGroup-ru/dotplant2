<?php
/**
 * @var $this \yii\web\View
 * @var $model \app\modules\shop\models\Yml
 **/
use \yii\helpers\Html;
use \kartik\icons\Icon;
use \app\backend\widgets\BackendWidget;

$this->title = Yii::t('app', 'Settings');
$this->params['breadcrumbs'][] = [
    'label' => 'YML',
    'url' => ['index']
];
$this->params['breadcrumbs'][] = $this->title;

$formName = 'YmlSettings';

\app\backend\assets\YmlAsset::register($this);

?>

<?= app\widgets\Alert::widget([
    'id' => 'alert',
]); ?>





<?php
$yml_relations = [
    'getImage' => \app\modules\image\models\Image::className(),
    'getCategory' => \app\modules\shop\models\Category::className(),
];

$yml_settings = [];
$yml_settings['product_fields'] = (new \app\modules\shop\models\Product())->attributeLabels();
$yml_settings['relations_keys'] = array_combine(array_keys($yml_relations), array_keys($yml_relations));
$yml_settings['relations_map'] = [];
foreach ($yml_relations as $key => $value) {
    $_fields = (new $value)->attributeLabels();
    $yml_settings['relations_map'][$key] = [
        'fields' => $_fields,
        'html' => array_reduce($_fields, function($result, $i) {
            $result .= '<option value="'.addslashes(htmlspecialchars($i)).'">'.addslashes(htmlspecialchars($i)).'</option>';
            return $result;
        }, ''),
    ];
}
if (true === isset ($yml_settings['relations_map']['getImage'])) {
    $yml_settings['relations_map']['getImage']['fields']['file'] = Yii::t('app', 'File');
    $yml_settings['relations_map']['getImage']['html'] .= '<option value="file">'.Yii::t('app', 'File').'</option>';
}
$prop_group = \app\models\Object::getForClass(\app\modules\shop\models\Product::className())->id;
$provider = (new \yii\db\Query())
    ->select(['pg.name as pgname', 'p.name', 'p.id', 'p.handler_additional_params'])
    ->from(\app\models\Property::tableName().' as p', \app\models\PropertyGroup::tableName().'as pg')
    ->leftJoin(\app\models\PropertyGroup::tableName().'as pg', 'pg.id=p.property_group_id')
    ->where(['pg.object_id' => $prop_group]);
$prop_group = $provider->all();
$yml_settings['properties_map'] = ['html' => '', 'fields' => []];
$yml_settings['properties_map'] = array_reduce(
    array_reduce(
        $prop_group,
        function($result, $i)
        {
            if (isset($result[$i['pgname']])) {
                $result[$i['pgname']]['html'] .= '<option value="'.addslashes(htmlspecialchars($i['id'])).'">'.addslashes(htmlspecialchars($i['name'])).'</option>';
                $result[$i['pgname']]['fields'][$i['pgname']][$i['id']] = $i['name'];
                $result[$i['pgname']]['name'] = $i['pgname'];
            }
            return $result;
        },
        array_fill_keys(array_unique(array_column($prop_group, 'pgname')), ['fields' => [], 'html' => '', 'name' => ''])
    ),
    function($result, $i)
    {
        $result['fields'] = array_replace($result['fields'], $i['fields']);
        $result['html'] .= '<optgroup label="'.addslashes(addslashes($i['name'])).'">'.addslashes(addslashes($i['html'])).'</optgroup>';
        return $result;
    },
    $yml_settings['properties_map']
);
?>

<?php $form = \kartik\widgets\ActiveForm::begin(['id' => 'yml-form', 'type' => \kartik\widgets\ActiveForm::TYPE_HORIZONTAL]); ?>
<div class="row">
    <div class="col-md-6">
        <?php BackendWidget::begin(['title'=> Yii::t('app', 'Settings for shop section'), 'icon' => 'cogs']); ?>
        <?= $form->field($model, 'shop_name'); ?>
        <?= $form->field($model, 'shop_company'); ?>
        <?= $form->field($model, 'shop_url'); ?>
        <?= $form->field($model, 'shop_local_delivery_cost'); ?>
        <?= $form->field($model, 'shop_store')->checkbox(); ?>
        <?= $form->field($model, 'shop_pickup')->checkbox(); ?>
        <?= $form->field($model, 'shop_delivery')->checkbox(); ?>
        <?= $form->field($model, 'shop_adult')->checkbox(); ?>
        <?php BackendWidget::end(); ?>
    </div>

    <div class="col-md-6">
        <?php BackendWidget::begin(['title'=> Yii::t('app', 'Settings for currencies section'), 'icon' => 'cogs']); ?>
        <?= $form->field($model, 'currency_id')->dropDownList([
            'RUR' => 'RUR',
            'USD' => 'USD',
            'EUR' => 'EUR',
            'UAH' => 'UAH',
            'KZT' => 'KZT',
        ]); ?>
        <?php BackendWidget::end(); ?>

        <?php BackendWidget::begin(['title'=> Yii::t('app', 'General settings'), 'icon' => 'cogs']); ?>
        <?= $form->field($model, 'general_yml_filename'); ?>
        <?= $form->field($model, 'general_yml_type')->dropDownList([
            'simplified' => Yii::t('app', 'The simplified description'),
            'vendor.model' => Yii::t('app', 'Any goods'),
            'book' => Yii::t('app', 'Books'),
            'audiobook' => Yii::t('app', 'Audiobooks'),
            'artist.title' => Yii::t('app', 'Musical and video production'),
            'tour' => Yii::t('app', 'Tours'),
            'event-ticket' => Yii::t('app', 'Tickets for event'),
        ]); ?>
        <?= $form->field($model, 'use_gzip')->checkbox(); ?>
        <?php BackendWidget::end(); ?>
    </div>
</div>

<div class="row">
    <div id="offers_section" class="col-md-12">
        <?php BackendWidget::begin(['title'=> Yii::t('app', 'Settings for offers section'), 'icon' => 'cogs']); ?>

        <?php
        foreach ($model->getOfferElements() as $el) {
            echo $form->field($model, $el)->render(
                function(\yii\widgets\ActiveField $field) use ($yml_settings)
                {
                    $value = $field->model->{$field->attribute};

                    $label = Html::activeLabel($field->model, $field->attribute, $field->labelOptions);

                    $input = '<div class="col-md-2">'.
                        Html::activeDropDownList(
                            $field->model,
                            $field->attribute . '[type]',
                            [
                                'field' => Yii::t('app', 'Field'),
                                'property' => Yii::t('app', 'Property'),
                                'relation' => Yii::t('app', 'Relation'),
                            ],
                            array_merge(['data-ymlselect' => 'type'], $field->inputOptions)
                        ).'</div>';

                    $_key_list = [];
                    if (!empty($value['key'])) {
                        if ('field' === $value['type']) {
                            $_key_list = $yml_settings['product_fields'];
                        } elseif ('property' === $value['type']) {
                            $_key_list = $yml_settings['properties_map']['fields'];
                        } elseif ('relation' === $value['type']) {
                            $_key_list = $yml_settings['relations_keys'];
                        }
                    }

                    $input .= '<div class="col-md-3">' .
                        Html::activeDropDownList(
                            $field->model,
                            $field->attribute . '[key]',
                            $_key_list,
                            array_merge(['data-ymlselect' => 'key'], $field->inputOptions)
                        ) . '</div>';

                    $_value_list = [];
                    if (!empty($value['value']) && 'relation' === $value['type']) {
                        $_value_list = $yml_settings['relations_map'][$value['key']]['fields'];
                    }
                    $input .= '<div class="col-md-3">' .
                        Html::activeDropDownList(
                            $field->model,
                            $field->attribute . '[value]',
                            $_value_list,
                            array_merge(['data-ymlselect' => 'value', 'style' => empty($_value_list)?'display:none;':''], $field->inputOptions)
                        ) . '</div>';

                    $error = Html::error($field->model, $field->attribute, $field->errorOptions);
                    return sprintf("%s\n<div class=\"col-md-10 offer-group\">%s</div>\n<div class=\"col-md-offset-2 col-md-10\">%s</div>", $label, $input, $error);
                }
            );
        }
        ?>
        <?= $form->field($model, 'offer_param')->checkbox(); ?>
        <?= Html::submitButton(
            Icon::show('save') . Yii::t('app', 'Save'),
            ['class' => 'btn btn-primary']
        ); ?>

        <?= Html::a(
            Icon::show('code') . Yii::t('app', 'Create YML'),
            ['create'],
            ['class' => 'btn btn-primary']
        ); ?>

        <?php
        if (is_file(\Yii::getAlias('@webroot').'/'.$model->general_yml_filename)) {
            echo Html::a(
                Icon::show('download') . Yii::t('app', 'Download'),
                \yii\helpers\Url::to($model->general_yml_filename, true),
                ['class' => 'btn btn-default']
            );
        }
        ?>
        <?php BackendWidget::end(); ?>
    </div>
    <div id="yml-properties-section" class="col-md-12">
        <?php
        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $provider,
        ]);
        echo \kartik\dynagrid\DynaGrid::widget([
            'options' => [
                'id' => 'Props-grid',
            ],
            'columns' => [
                [
                    'class' => 'yii\grid\DataColumn',
                    'attribute' => 'id',
                ],
                [
                    'class' => 'yii\grid\DataColumn',
                    'attribute' => 'pgname',
                    'label' => Yii::t('app', 'Property Group ID'),
                ],
                [
                    'class' => 'yii\grid\DataColumn',
                    'attribute' => 'name',
                    'label' => Yii::t('app', 'Property name'),
                ],
                [
                    'class' => 'yii\grid\DataColumn',
                    'format' => 'raw',
                    'label' => Yii::t('app', 'Use in YML file'),
                    'value' => function ($model, $key, $index, $column) {
                        $data = \yii\helpers\Json::decode($model['handler_additional_params']);
                        return Html::checkbox(
                            'use_in_file',
                            (isset($data['use_in_file']) && $data['use_in_file'] == 1) ? true : false,
                            [
                                'class' => 'form-control',
                                'data-id' => $model['id'],
                                'data-type' => 'ajax-input',
                            ]
                        );
                    },
                ],
                [
                    'class' => 'yii\grid\DataColumn',
                    'format' => 'raw',
                    'label' => Yii::t('app', 'Measure'),
                    'value' => function ($model, $key, $index, $column) {
                        $data = \yii\helpers\Json::decode($model['handler_additional_params']);
                        return Html::input(
                            'text',
                            'unit',
                            isset($data['unit']) ? $data['unit'] : '',
                            [
                                'class' => 'form-control',
                                'data-id' => $model['id'],
                                'data-type' => 'ajax-input',
                            ]
                        );
                    },
                ],
            ],
            'theme' => 'panel-default',
            'gridOptions' => [
                'dataProvider' => $dataProvider,
                'hover' => true,
                'panel' => [
                    'heading' => '<h3 class="panel-title">' . Yii::t('app', 'Properties import to YML') . '</h3>',
                ],
            ]
        ]);
        ?>
    </div>
</div>
<?php \kartik\widgets\ActiveForm::end(); ?>

<?php $this->beginBlock('jsValues') ?>
    var $url = '<?= \yii\helpers\Url::toRoute(['/shop/backend-yml/save-property-unit'])?>';
    var ymlSelectFields = '<?= array_reduce(
        $yml_settings['product_fields'],
        function($result, $i) {
            $result .= '<option value="'.addslashes(htmlspecialchars($i)).'">'.addslashes(htmlspecialchars($i)).'</option>';
            return $result;
        }, ''); ?>';
    var ymlSelectRelKeys = '<?= array_reduce(
        $yml_settings['relations_keys'],
        function($result, $i) {
            $result .= '<option value="'.addslashes(htmlspecialchars($i)).'">'.addslashes(htmlspecialchars($i)).'</option>';
            return $result;
        }, ''); ?>';
    var ymlSelectRelations = {
    <?php
    foreach ($yml_settings['relations_map'] as $k => $v) {
        echo $k.': \''.$v['html'].'\','.PHP_EOL;
    }
    ?>
    };
    var ymlSelectProperties = '<?= $yml_settings['properties_map']['html']; ?>';
<?php $this->endBlock(); ?>

<?php $this->registerJs($this->blocks['jsValues'], \yii\web\View::POS_HEAD) ; ?>
