<?php
/**
 * @var $this \yii\web\View
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
                $result .= '<option value="'.htmlspecialchars($i).'">'.htmlspecialchars($i).'</option>';
                return $result;
            }, ''),
        ];
    }

    $prop_group = \app\models\Object::getForClass(\app\modules\shop\models\Product::className())->id;
    $prop_group = (new \yii\db\Query())
        ->select(['pg.name as pgname', 'p.name', 'p.id'])
        ->from(\app\models\Property::tableName().' as p', \app\models\PropertyGroup::tableName().'as pg')
        ->leftJoin(\app\models\PropertyGroup::tableName().'as pg', 'pg.id=p.property_group_id')
        ->where(['pg.object_id' => $prop_group])
        ->all();

    $yml_settings['properties_map'] = ['html' => '', 'fields' => []];
    $yml_settings['properties_map'] = array_reduce(
        array_reduce(
            $prop_group,
            function($result, $i)
            {
                if (isset($result[$i['pgname']])) {
                    $result[$i['pgname']]['html'] .= '<option value="'.htmlspecialchars($i['id']).'">'.htmlspecialchars($i['name']).'</option>';
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
            $result['html'] .= '<optgroup label="'.$i['name'].'">'.$i['html'].'</optgroup>';
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
        <?= $form->field($model, 'offer_param')->checkbox(); ?>
        <?= $form->field($model, 'general_yml_type')->dropDownList([
            'simplified' => Yii::t('app', 'The simplified description'),
            'vendor.model' => Yii::t('app', 'Any goods'),
            'book' => Yii::t('app', 'Books'),
            'audiobook' => Yii::t('app', 'Audiobooks'),
            'artist.title' => Yii::t('app', 'Musical and video production'),
            'tour' => Yii::t('app', 'Tours'),
            'event-ticket' => Yii::t('app', 'Tickets for event'),
        ]); ?>
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

        <?= Html::submitButton(
            Icon::show('save') . Yii::t('app', 'Save'),
            ['class' => 'btn btn-primary']
        ); ?>

        <?= Html::a(
            Icon::show('code') . Yii::t('app', 'Create YML'),
            ['create'],
            ['class' => 'btn btn-primary']
        ); ?>
        <?php BackendWidget::end(); ?>
    </div>
</div>
<?php \kartik\widgets\ActiveForm::end(); ?>

<?php $this->beginBlock('jsValues') ?>
    ymlSelectFields = '<?= array_reduce(
    $yml_settings['product_fields'],
    function($result, $i) {
        $result .= '<option value="'.htmlspecialchars($i).'">'.htmlspecialchars($i).'</option>';
        return $result;
    }, ''); ?>';

    ymlSelectRelKeys = '<?= array_reduce(
    $yml_settings['relations_keys'],
    function($result, $i) {
        $result .= '<option value="'.htmlspecialchars($i).'">'.htmlspecialchars($i).'</option>';
        return $result;
    }, ''); ?>';

    ymlSelectRelations = {
<?php
foreach ($yml_settings['relations_map'] as $k => $v) {
    echo $k.': \''.$v['html'].'\','.PHP_EOL;
}
?>
    };

    ymlSelectProperties = '<?= $yml_settings['properties_map']['html']; ?>';
<?php $this->endBlock(); ?>

<?php $this->registerJs($this->blocks['jsValues'], \yii\web\View::POS_HEAD) ; ?>
