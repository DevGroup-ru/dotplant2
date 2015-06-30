<?php
/** @var app\components\WebView $this */
/** @var boolean $isInSidebar */
/** @var boolean $hideEmpty */
/** @var \app\modules\shop\models\FilterSets[] $filterSets */
/** @var boolean $displayHeader */
/** @var string $header  */
/** @var string $id */

use app\models\Object;
use app\modules\shop\models\Product;
use yii\db\Query;
use yii\helpers\Html;
use yii\helpers\Url;

$sidebarClass = $isInSidebar ? 'sidebar-widget' : '';
echo '<div class="filter-sets-widget ' . $sidebarClass . '">';
if ($displayHeader === true) {
    ?>
    <div class="widget-header">
        <?= $header ?>
    </div>
    <?php
}
?>

    <div class="filters" id="<?=$id?>">

<?php
$urlParams = ['/shop/product/list','properties'=>[],'last_category_id'=>1];
if (isset($_GET['properties'])) {
    $urlParams['properties'] = $_GET['properties'];
}
if (isset($_GET['last_category_id'])) {
    $urlParams['last_category_id'] = $_GET['last_category_id'];
}
// Checked items
$checkedIds = [];
foreach ($urlParams['properties'] as $propertyId => $values) {
    $values = (array) $values;
    foreach ($values as $selectionId) {
        $checkedIds[] = '#filter-check-'.$selectionId;
    }
}
$checkedIds = implode(',', $checkedIds);
if (!Yii::$app->request->isAjax) {
    $js = <<<JS
$('$checkedIds').prop('checked', true);
$('#$id').dotPlantSmartFilters();

JS;
    $this->registerJs($js);
}

?>
    <form action="<?=Url::to(['/shop/product/list', 'last_category_id'=>$urlParams['last_category_id']])?>" method="post" class="filter-form">
<?php
//$urlParams = \yii\helpers\ArrayHelper::merge(['/shop/product/list'], $_GET);
$cacheParams = [
    'duration'=>86400,
    'dependency' => new \yii\caching\TagDependency([
        'tags' => \devgroup\TagDependencyHelper\ActiveRecordHelper::getCommonTag(\app\modules\shop\models\FilterSets::className())
    ])
];
if ($this->beginCache('FilterSets:'.json_encode($urlParams).':'.(int) $hideEmpty, $cacheParams)) {
    foreach ($filterSets as $set) {
        $property = $set->getProperty();

        if ($property->has_static_values) {
            $selections = \app\models\PropertyStaticValues::getValuesForFilter(
                $property->id,
                $urlParams['last_category_id'],
                $urlParams['properties']
            );


            if (count($selections) === 0) {
                continue;
            }
            echo '<div class="filter-property">';
            echo '<div class="property-name">' . Html::encode($property->name) . '</div>';
            echo '<ul class="property-values">';
            foreach ($selections as $selection) {
                /** @var \app\models\PropertyStaticValues $selection */
                $params = $urlParams;
                $params['properties'][$property->id] = [$selection['id']];


                $url = Url::to($params);
                echo '<li>'
                    . Html::checkbox(
                        'properties[' . $property->id . '][]',
                        false,
                        [
                            'value' => $selection['id'],
                            'class' => 'filter-check filter-check-property-' . $property->id,
                            'id' => 'filter-check-' . $selection['id'],
                            'data-property-id' => $property->id,
                        ]
                    )
                    . Html::a(
                        $selection['name'],
                        $url,
                        [
                            'class' => 'filter-link',
                            'data-selection-id' => $selection['id'],
                            'data-property-id' => $property->id,
                        ]
                    )
                    . '</li>';
            }
            echo '</ul>';
            echo '</div>';
        }
    }
    $this->endCache();
}
?>


<?php
echo '<div class="filter-actions">';
echo Html::submitButton(
    Yii::t('app', 'Show'),
    [
        'class' => 'btn btn-primary btn-filter-show',
    ]
);
echo '</div>';
if (Yii::$app->request->isAjax) {
    echo '<script>$("' . $checkedIds . '").prop("checked", true);</script>';
}
echo '</form>';
echo '</div>';
echo '</div>';
