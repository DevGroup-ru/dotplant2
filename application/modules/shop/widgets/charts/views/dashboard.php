<?php
/**
 * @var $userData []
 * @var $salesChart []
 * @var $statistics []
 * @var $this \yii\web\View
 */

if (false === empty($salesChart)) {
    $JS = "var salesHeader = '" . addslashes($salesChart['salesHeader']) . "', "
        . "tooltipTpl = '" . addslashes($salesChart['tooltipTpl']) . "',"
        . "dateFormat = '" . addslashes($salesChart['dateFormat']) . "',"
        . "jsOrders = {$salesChart['jsOrders']};";
    $this->registerJs($JS, \yii\web\View::POS_HEAD);
}
?>
<div class="jarviswidget" id="wid-id-charts" data-widget-editbutton="true" data-widget-deletebutton="false">
    <header>
        <span class="widget-icon"> <i class="fa fa-bar-chart-o"></i> </span>

        <h2><?= $salesChart['salesHeader'] ?></h2>
    </header>
    <div>
        <div class="jarviswidget-editbox">
        </div>
        <div class="widget-body no-padding">
            <div id="saleschart" class="chart"></div>
        </div>
    </div>
</div>

<?php if (false === empty($statistics)) : ?>
    <div class="jarviswidget jarviswidget-color-blueDark" id="wid-id-stats" data-widget-editbutton="false">
        <header>
            <span class="widget-icon"> <i class="fa fa-table"></i> </span>

            <h2><?= Yii::t('app', 'Statistics') ?></h2>
        </header>
        <div>
            <div class="jarviswidget-editbox">
            </div>
            <div class="widget-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tbody>
                        <?php foreach ($statistics as $label => $data) : ?>
                            <tr>
                                <td><?= $label ?></td>
                                <td><?= $data ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php if (false === empty($userData)) : ?>
    <div class="jarviswidget jarviswidget-color-blueDark" id="wid-id-users" data-widget-editbutton="false">
        <header>
            <span class="widget-icon"> <i class="fa fa-table"></i> </span>

            <h2><?= Yii::t('app', 'Users') ?></h2>
        </header>
        <div>
            <div class="jarviswidget-editbox">
            </div>
            <div class="widget-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tbody>
                        <?php foreach ($userData as $label => $data) : ?>
                            <tr>
                                <td><?= $label ?></td>
                                <td><?= $data ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
