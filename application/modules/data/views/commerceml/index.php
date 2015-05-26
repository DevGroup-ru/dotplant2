<?php
    use yii\helpers\Html;
    use app\backend\widgets\BackendWidget;

    $this->title = Yii::t('app', 'CommerceML');
    $this->params['breadcrumbs'][] = $this->title;
?>

<?= \app\widgets\Alert::widget() ?>

<?php
    BackendWidget::begin();
    echo Html::beginForm('', 'post', ['enctype' => 'multipart/form-data', 'class' => 'form-horizontal']);
?>
    <div class="form-group">
        <label class="col-md-2 control-label" for="cmlFile">File import</label>
        <div class="">
            <?= Html::fileInput('cmlFile[one]', null, ['class' => 'btn btn-default']); ?>
        </div>
    </div>

    <div class="form-group">
        <label class="col-md-2 control-label" for="cmlFile">File offers</label>
        <div class="">
            <?= Html::fileInput('cmlFile[two]', null, ['class' => 'btn btn-default']); ?>
        </div>
    </div>

    <div class="form-group">
        <div class="col-md-3 col-md-offset-2">
            <?= Html::submitButton(Yii::t('app', 'Import'), ['class' => 'btn btn-primary']); ?>
            <?= Html::a(Yii::t('app', 'Configure'), 'configure', ['class' => 'btn btn-warning']); ?>
        </div>
    </div>
<?php
    echo Html::endForm();
    BackendWidget::end();
?>
