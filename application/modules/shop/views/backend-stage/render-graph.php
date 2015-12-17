<?php
/**
 * @var \yii\web\View $this
 */

use yii\helpers\Json;
use yii\helpers\Url;

$this->title = Yii::t('app', 'Order stages graph');
$this->params['breadcrumbs'] = [
    [
        'label' => Yii::t('app', 'Order stage subsystem'),
        'url' => Url::to(['index']),
    ],
    $this->title,
];
$this->registerJsFile('https://www.gstatic.com/charts/loader.js');
$jsonData = Json::encode($stages);
?>
    <div id="chart_div"></div>

<?php
$js = <<<JS
google.charts.load('current', {packages:["orgchart"]});
      google.charts.setOnLoadCallback(drawChart);

      function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Id');
        data.addColumn('string', 'ParentId');
        data.addColumn('string', 'Event');
        // For each orgchart box, provide the name, manager, and tooltip to show.
        data.addRows({$jsonData});

        // Create the chart.
        var chart = new google.visualization.OrgChart(document.getElementById('chart_div'));
        // Draw the chart, setting the allowHtml option to true for the tooltips.
        chart.draw(data, {allowHtml:true});
      }
JS;

$this->registerJs($js);
$this->registerCss(
    '#chart_div table{border-collapse:inherit;}'
);


