<?php
/**
 * @var array $userData
 * @var array $salesChart
 * @var array $statistics
 * @var \yii\web\View $this
 */

    $this->registerJs($salesChart['js'], \yii\web\View::POS_HEAD);
?>
<div class="jarviswidget" id="wid-id-charts" data-widget-editbutton="true" data-widget-deletebutton="false">
    <header>
        <span class="widget-icon"> <i class="fa fa-bar-chart-o"></i> </span>

        <h2><?= $salesChart['header'] ?></h2>
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
                        <?php foreach ($statistics as $shopLabel => $shopValues): ?>
                            <tr><td colspan="2"><h6><?= $shopLabel; ?></h6></td></tr>
                            <?php foreach ($shopValues as $label => $data) : ?>
                                <tr>
                                    <td><?= $label ?></td>
                                    <td><?= $data ?></td>
                                </tr>
                            <?php endforeach; ?>
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
                        <?php foreach ($userData as $shopLabel => $shopValues): ?>
                            <tr><td colspan="2"><h6><?= $shopLabel; ?></h6></td></tr>
                            <?php foreach ($shopValues as $label => $data) : ?>
                                <tr>
                                    <td><?= $label ?></td>
                                    <td><?= $data ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<style>
    #saleschart > div.legend > div {
        top: 0!important;
    }
    #saleschart > div.legend > table {
        top: 0!important;
    }
</style>
