<?php
namespace app\modules\shop\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\icons\Icon;
use app\modules\shop\assets\PacketPriceEditAsset;
use app\modules\shop\models\Currency;

/**
* Packet edit the prices
* @param $context string
* @param $btnHtmlOptions []
* @param $gridSelector string
* @param $modalFormId string
* @param $modalView string
*/
class PacketPriceEditButton extends Widget
{
	const PACKET_PRICE_EDIT = 'edit_prices';

	public $context;
	public $btnHtmlOptions = [];
	public $gridSelector = '.grid-view';
	public $modalFormId = 'packet-price-edit-form';
	public $modalView = '@app/modules/shop/widgets/views/PacketPriceEditModal';

	public function init()
	{
		$this->btnHtmlOptions['data-action'] = self::PACKET_PRICE_EDIT;
		if (!isset($this->btnHtmlOptions['class']))
			$this->btnHtmlOptions['class'] = 'btn btn-default';

		$this->regJS();
		PacketPriceEditAsset::register($this->view);
	}

	public function run()
	{
		// render modal form
		$modal = $this->renderModal();

		// render button
		$button = Html::button(
			Icon::show('usd') . ' ' .
			\Yii::t('app', 'Edit prices'),
			$this->btnHtmlOptions
		);
		
		return $button . "\n" . $modal;
	}

	protected function renderModal() {
		$currencies = [];
		$arCurrency = Currency::find()->orderBy(['is_main' => SORT_DESC])->all();
		foreach ($arCurrency as $currency)
			$currencies[$currency->id] = $currency->iso_code;


		return	$this->view->render($this->modalView, [
					'modalFormId' => $this->modalFormId,
					'currencies' => $currencies,
					'contextId' => $this->context
				]);
	}

	protected function regJS() {
		$lang = [
			'confirm_edit' => Yii::t('app', 'The action is irreversible. Do you want to continue?'),
			'wait' => Yii::t('app', 'Waiting'),
			'total' => Yii::t('app', 'Total items'),
			'updated' => Yii::t('app', 'Updated at'),
			'missed' => Yii::t('app', 'Missed (difference between the currencies)'),
			'errors' => Yii::t('app', 'Number of errors'),
		];

		$this->view->registerJs("
			$(\"button[data-action='" . self::PACKET_PRICE_EDIT . "']\").click(function(){
				var items = $('{$this->gridSelector}').yiiGridView('getSelectedRows');
				if (items.length) {
					var modal = $('#{$this->modalFormId}'),
						action = $(this).data('action');

					modal.data('url', '" . Url::toRoute(['packet-price-edit']) . "')
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
