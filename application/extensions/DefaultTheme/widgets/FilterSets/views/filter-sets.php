<?php
/** @var boolean $isInSidebar */
/** @var \app\modules\shop\models\FilterSets[] $filterSets */
/** @var boolean $display_header */
/** @var string $header  */
/** @var string $id */

use yii\helpers\Html;
use yii\helpers\Url;

$sidebarClass = $isInSidebar ? 'sidebar-widget' : '';
echo '<div class="filter-sets-widget ' . $sidebarClass . '">';
if ($display_header === true) {
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
?>
    <form action="<?=Url::to(['/shop/product/list', 'last_category_id'=>$urlParams['last_category_id']])?>" method="post" class="filter-form">
<?php
//$urlParams = \yii\helpers\ArrayHelper::merge(['/shop/product/list'], $_GET);
foreach ($filterSets as $set) {
    $property = $set->getProperty();

    if ($property->has_static_values) {
        $selections = \app\models\PropertyStaticValues::getValuesForPropertyId($property->id);
        $selections = array_filter($selections, function($val){
            return $val['dont_filter'] === '0';
        });
        if (count($selections) === 0) {
            continue;
        }
        echo '<div class="filter-property">';
        echo '<div class="property-name">' . Html::encode($property->name) . '</div>';
        echo '<ul class="property-values">';
        foreach ($selections as $selection) {
            /** @var \app\models\PropertyStaticValues $selection */
            $params = $urlParams;
            $params['properties'][$property->id] = $selection['id'];
            $checked = false;

            if (isset($urlParams['properties'][$property->id])) {
                if (in_array($selection['id'], (array)$urlParams['properties'][$property->id])) {
                    $checked = true;
                }
            }

            $url = Url::to($params);
            echo '<li>'
                . Html::checkbox(
                    'properties['.$property->id.'][]',
                    $checked ,
                    [
                        'value'=>$selection['id'],
                        'class'=>'filter-check filter-check-property-'.$property->id,
                        'id' => 'filter-check-' . $selection['id'],
                        'data-property-id' => $property->id,
                    ]
                )
                . Html::a(
                    $selection['name'],
                    $url,
                    [
                        'class'=>'filter-link',
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
echo '</form>';
echo '</div>';
echo '</div>';

//$delayThreshold = 500;
//$distanceTreshold = 50;

$js = <<<JS
$('#$id').dotPlantSmartFilters();
JS;
$this->registerJs($js);