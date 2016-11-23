<?php

namespace app\widgets;

use yii\jui\JuiAsset;
use yii\base\Widget;
use yii\web\JsExpression;
use yii\web\View;
use Yii;

class MultiSelect extends Widget
{
    private static $count = 0;
    public $addUrl;
    public $ajax = true;
    public $ajaxData = [];
    public $attribute;
    public $defaultIndex = -1;
    public $defaultLabel = 'Choose item';
    public $errorFunction = 'function(data){}';
    public $id;
    public $items = [];
    public $label = '';
    public $model;
    public $name;
    public $removeUrl;
    public $selectedItems = [];
    public $sortable = false;
    public $successFunction = 'function(data){}';

    /**
     * @param $list
     * @return array
     */
    private function getSelectedItems($list)
    {
        $result = [];
        foreach ($list as $key => $item) {
            if (is_array($item)) {
                $result += $this->getSelectedItems($item);
            } else {
                if (in_array($key, $this->selectedItems)) {
                    $result[$key] = $item;
                }
            }
        }
        if ($this->sortable) {
            $sorted = [];
            foreach ($this->selectedItems as $key) {
                $sorted[$key] = $result[$key];
            }
            return $sorted;
        } else {
            return $result;
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->defaultLabel = Yii::t('app', $this->defaultLabel);
        self::$count++;
        if ($this->id === null) {
            $this->id = 'multi-select-'.self::$count;
        }
        if ($this->name === null) {
            $this->name = $this->model->formName().'['.$this->attribute.'][]';
        }
        $list = [$this->defaultIndex => $this->defaultLabel] + $this->items;
        $table = $this->getSelectedItems($this->items);
        $params = [
            'list' => $list,
            'table' => $table,
            'label' => $this->label,
            'id' => $this->id,
            'name' => $this->name,
            'ajax' => $this->ajax,
            'sortable' => $this->sortable,
        ];
        $view = $this->getView();
        // @todo
        // $errorFunction =
        $json = \yii\helpers\Json::encode([
            'addUrl' => $this->addUrl,
            'ajax' => $this->ajax,
            'defaultIndex' => $this->defaultIndex,
            'removeUrl' => $this->removeUrl,
            'selectedItems' => $this->selectedItems,
            'ajaxData' => $this->ajaxData,
            'errorFunction' => new JsExpression($this->errorFunction),
            'successFunction' => new JsExpression($this->successFunction),
        ]);
        if (self::$count == 1) {
            $view = $this->getView();
            JuiAsset::register($view);
            $view->registerJs(
                "jQuery.fn.multiSelect = function(params){
                var \$multiSelect = \$(this);
                for(var key in params.selectedItems){
                    \$multiSelect.find('select.list option[value=\"' + params.selectedItems[key] + '\"]').eq(0).addClass('hidden');
                }
                \$multiSelect.on('click', 'table a.remove', function(){
                    \$this = \$(this);
                    var \$tr = \$this.parents('tr').eq(0);
                    var \$td = \$tr.find('td').eq(0);
                    if(params.ajax){
                        var data = params.ajaxData;
                        data.id = \$tr.attr('data-id');
                        \$.ajax({
                            'data' : data,
                            'dataType' : 'json',
                            'success' : function(data){
                                if(data.status == 1){
                                    \$multiSelect.find('select.list').find('option[value=\"' + \$tr.attr('data-id') + '\"]').removeClass('hidden');
                                    \$tr.remove();
                                    params.successFunction(data);
                                }else{
                                    params.errorFunction(data);
                                }
                            },
                            'type' : 'get',
                            'url' : params.removeUrl
                        });
                    }else{
                        \$multiSelect.find('select.hidden').find('option[value=\"' + \$tr.attr('data-id') + '\"]').remove();
                        \$tr.remove();
                        \$multiSelect.find('select.list').find('option[value=\"' + \$tr.attr('data-id') + '\"]').removeClass('hidden');
                    }
                    return false;
                }).on('change', 'select', function(){
                    var \$this= \$(this);
                    if(\$this.val() != params.defaultIndex){
                        var \$item = \$this.find('option[value=\"' + \$this.val() + '\"]');
                        var \$table = \$multiSelect.find('table').eq(0);
                        var \$clone = \$table.find('tr.hidden').eq(0).clone().removeClass('hidden').attr('data-id', \$this.val());
                        \$clone.find('td').eq(0).text(\$item.text());
                        \$input = \$('<input type=hidden>').val(\$this.val()).attr(\"name\", \"" . $this->name . "\");
                        \$clone.append(\$input);
                        if(params.ajax){
                            var data = params.ajaxData;
                            data.id = \$this.val();
                            \$.ajax({
                                'data' : data,
                                'dataType' : 'json',
                                'success' : function(data){
                                    if(data.status == 1){
                                        \$item.addClass('hidden');
                                        \$clone.appendTo(\$table);
                                        \$this.find('option[value=\"' + params.defaultIndex + '\"]').attr('selected', 'selected');
                                        params.successFunction(data);
                                    }else{
                                        params.errorFunction(data);
                                    }
                                },
                                'type' : 'get',
                                'url' : params.addUrl
                            });
                        }else{
                            \$('<option></option>').attr('value', \$this.val()).attr('selected', 'selected').text(\$item.text()).appendTo(\$multiSelect.find('select.hidden').eq(0));
                            \$item.addClass('hidden');
                            \$clone.appendTo(\$table);
                        }
                    }
                });
                }",
                View::POS_END
            );
        }
        $view->registerJs("jQuery('#".$this->id."').multiSelect(".$json.");");
        if ($this->sortable) {
            $view->registerJs("jQuery('#".$this->id." table tbody').sortable({placeholder: \"ui-state-highlight\"});");
        }
        echo $this->render('multiSelect', $params);
    }
}
