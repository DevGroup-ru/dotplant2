<?=  app\widgets\Alert::widget([
    'id' => 'alert',
]); ?>

<?php if (count($emailsBad) > 0) { ?>
    <h4><?= Yii::t('app', 'email address, which will not come letters') ?>:</h4>
    <?php foreach ($emailsBad as $bad) { ?>
        <div class="row">
            <div class="col-md-3 col-md-offset-1"><?= $bad->name ?></div>
            <div class="col-md-3"><?= $bad->email ?></div>
        </div>
    <?php } ?>
<?php } ?>

<hr/>

<?php if (count($emailsOk) > 0) { ?>
    <h4><?= Yii::t('app', 'Mailing ok') ?>:</h4>
    <?php foreach ($emailsOk as $ok) { ?>
        <div class="row">
            <div class="col-md-3 col-md-offset-1"><?= $ok->name ?></div>
            <div class="col-md-3"><?= $ok->email ?></div>
        </div>
    <?php } ?>
<?php } ?>