<?= app\widgets\Alert::widget([
    'id' => 'alert',
]); ?>

<?php if (isset($model)) { ?>
    <div class="row">
        <div class="col-md-2 col-md-offset-1">
            name: <?= $model->name ?>
        </div>
        <div class="col-md-4 col-md-offset-1">
            email: <?= $model->email ?>
        </div>
    </div>
<?php } ?>
