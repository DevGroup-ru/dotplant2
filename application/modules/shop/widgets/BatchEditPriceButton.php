<?php
namespace app\modules\shop\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\icons\Icon;
use app\modules\shop\assets\BatchEditPriceAsset;
use app\modules\shop\models\Currency;

/**
 * Batch edit prices
 * @param $context string
 * @param $btnHtmlOptions []
 * @param $gridSelector string
 * @param $modalFormId string
 * @param $modalView string
 */
class BatchEditPriceButton extends Widget
{
    const BATCH_EDIT_PRICE = 'edit_prices';

    public $context;
    public $btnHtmlOptions = [];
    public $gridSelector = '.grid-view';
    public $modalView = '@app/modules/shop/widgets/views/BatchEditPriceModal';
    private $modalFormId = 'batch-edit-price-form';

    public function init()
    {
        $this->btnHtmlOptions['data-action'] = self::BATCH_EDIT_PRICE;
        if (!isset($this->btnHtmlOptions['class'])) {
            $this->btnHtmlOptions['class'] = 'btn btn-default';
        }

        $this->regJS();
        BatchEditPriceAsset::register($this->view);
    }

    public function run()
    {
        // render modal form
        $modal = $this->renderModal();

        // render button
        $button = Html::button(
            Icon::show('usd') . ' ' . \Yii::t('app', 'Edit prices'),
            $this->btnHtmlOptions
        );
        
        return $button . "\n" . $modal;
    }

    protected function renderModal()
    {
        $currencies = [];
        $arCurrency = Currency::find()->orderBy(['is_main' => SORT_DESC])->all();
        foreach ($arCurrency as $currency) {
            $currencies[$currency->id] = $currency->iso_code;
        }

        return $this->view->render(
            $this->modalView,
            [
                'modalFormId' => $this->modalFormId,
                'currencies' => $currencies,
                'contextId' => $this->context
            ]
        );
    }

    protected function regJS()
    {
        $lang = [
            'confirm_edit' => Yii::t('app', 'The action is irreversible. Do you want to continue?'),
            'wait' => Yii::t('app', 'Waiting'),
            'total' => Yii::t('app', 'Total items'),
            'updated' => Yii::t('app', 'Updated at'),
            'missed' => Yii::t('app', 'Missed (difference between the currencies)'),
            'errors' => Yii::t('app', 'Number of errors'),
        ];

        $this->view->registerJs("
            $(\"button[data-action='" . self::BATCH_EDIT_PRICE . "']\").click(function() {
                var items = $('{$this->gridSelector}').yiiGridView('getSelectedRows');
                if (items.length) {
                    var modal = $('#{$this->modalFormId}'),
                        action = $(this).data('action');

                    modal.data('url', '" . Url::toRoute(['batch-edit-price']) . "')
                        .data('items', items)
                        .data('price-edit-action', action)
                        .data('lang', " . json_encode($lang) . ")
                        .modal('show');
                }
                return false;
            });
        ");
    }
}
