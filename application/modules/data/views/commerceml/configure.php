<?php
    use yii\helpers\Html;
    use app\backend\widgets\BackendWidget;

    $this->title = Yii::t('app', 'CommerceML configure');
    $this->params['breadcrumbs'][] = [
        'label' => Yii::t('app', 'CommerceML'),
        'url' => ['index']
    ];
    $this->params['breadcrumbs'][] = 'Configure';
?>

<?= \app\widgets\Alert::widget() ?>

<?php
    BackendWidget::begin();
    echo Html::beginForm('', 'post', ['enctype' => 'multipart/form-data', 'class' => 'form-horizontal']);
?>
    <div class="form-group">
        <label class="col-md-2 control-label" for="cmlFile">File</label>
        <div class="">
            <?= Html::fileInput('cmlFile', null, ['class' => 'btn btn-default']); ?>
        </div>
    </div>

    <div class="form-group">
        <div class="col-md-3 col-md-offset-2">
            <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-primary', 'data-bind' => 'click: clickSubmit']); ?>
        </div>
    </div>

    <div class="form-group">
        <div class="col-md-10 col-md-offset-1">
            <table class="table table-bordered table-striped">
                <tbody data-bind="template: {name: 'koTplPropTable', foreach: productsProperties, as: 'guid'}"></tbody>
            </table>
        </div>
    </div>
<?php
    echo Html::endForm();
    BackendWidget::end();
?>

<script type="text/html" id="koTplPropTable">
    <tr>
        <td data-bind="text: name"></td>
        <td data-bind="template: {name: 'koTplPropSelect'}"></td>
    </tr>
</script>

<script type="text/html" id="koTplPropSelect">
    <select
        class="form-control"
        data-bind="options: $root.modelsProperties, optionsText: 'name', optionsValue: 'id', value: guid.model_id, attr: {name: selectName}"
    ></select>
</script>

<script>
    (function(){
        var dataPropsGuid = '<?= \yii\helpers\Json::encode(array_values($props)); ?>';
        var dataProps = '<?= \yii\helpers\Json::encode(array_values($propsGroups)); ?>';

        function modelItemPropertyGuid(id, name, model_id) {
            this.id = id;
            this.name = name;
            this.model_id = model_id;
            this.selectName = 'guidSelect['+id+']';
        }

        function Cml(json) {
            var self = this;
            self.clickSubmit = function(data, event) {
//                event.preventDefault();
                return true;
            }
        }

        Cml.prototype.productsProperties = ko.observableArray(ko.utils.arrayMap(ko.utils.parseJson(dataPropsGuid), function(item) {
            return new modelItemPropertyGuid(item.id, item.name, item.model_id);
        }));

        Cml.prototype.modelsProperties = ko.observableArray(ko.utils.arrayMap(ko.utils.parseJson(dataProps), function(item) {
            return {id: item.id, name: item.name};
        }));

        ko.applyBindings(new Cml(dataProps));
    })();
</script>

