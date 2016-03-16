<?php
namespace app\modules\shop\actions;

use Yii;
use yii\base\Action;
use yii\web\Response;
use app\modules\shop\models\Category;
use app\modules\shop\models\Currency;
use app\modules\shop\models\Product;

class PacketPriceEditAction extends Action
{
	protected $pRound = []; // params of round

	public function run()
	{
		if (false === Yii::$app->request->isAjax) {
            throw new NotFoundHttpException();
        }
        ini_set('max_execution_time', 0);
        Yii::$app->response->format = Response::FORMAT_JSON;

        Yii::$app->log->flushInterval = 100;
        Yii::$app->log->traceLevel = 0;
        Yii::$app->log->targets = [
        	new yii\log\FileTarget([
	            'levels' => ['error'],
	            'exportInterval' => 100,
	        ])
        ];

        return $this->editPrices(
        	Yii::$app->request->post('items'),
        	Yii::$app->request->post('context')
        );
	}

	/**
	* Gets products from selected categories
	* @param $list int[]
	* @return int[]
	*/
	protected function getParentCategories($list) {
		$incChild = Yii::$app->request->post('is_child_inc');
		$count = count($list);

		// read child cats
		if ($incChild) {
			for ($i = 0; $i < $count; $i++) {
				$cats = Category::getByParentId($list[$i]);
				foreach ($cats as $category) {
					$list[] = $category->id;
					$count ++;
				}
				unset($cats);
			}
		}
		
		return $list;
	}

	/**
	* Set the algorithm of calculation
	* @return float function(float, float)
	*/
	protected function getCalculator() {
		$kind = Yii::$app->request->post('kind');
	 	$operation = Yii::$app->request->post('operation');

		if ($kind == 'fixed') { // fixed value
	 		if ($operation == 'inc')
	 			$calculator = function($subj, $value) {
	 				return $subj + $value;
	 			};
	 		else
	 			$calculator = function($subj, $value) {
	 				return $subj - $value;
	 			};
	 	} else { // percent value
	 		if ($operation == 'inc')
	 			$calculator = function($subj, $value) {
	 				return  $subj * (1 + $value / 100);
	 			};
	 		else
	 			$calculator = function($subj, $value) {
	 				return  $subj * (1 - $value / 100);
	 			};
	 	}

	 	return $calculator;
	}

	/**
	* To compare with zero and round
	* @param &$price float - $price can be rounded
	* @return bool
	*/
	protected function checkAndRound(&$price) {
		if ($price >= 0) {
	 		if ($this->pRound['is_round'])
	 			$price = round(
	 				$price,
	 				$this->pRound['round_val']
	 			);
 		} else {
 			return false;
 		}

 		return true;
	}

	/**
	* Main function. Change prices and saving it
	* @param $data int[]
	* @param $context string
	* @return mixed[]
	*/
	protected function editPrices($data, $context) {
		$calculator = $this->getCalculator();
		$selectedField = Yii::$app->request->post('apply_for');
		$value = Yii::$app->request->post('value');
		$type = Yii::$app->request->post('type');
		$currencyId = Yii::$app->request->post('currency_id');
		$this->pRound = [
			'is_round' => Yii::$app->request->post('is_round'),
			'round_val' => Yii::$app->request->post('round_val'),
		];

		$report = [
			'all' => 0,
			'success' => 0,
			'error' => 0,
			'skipped' => 0,
			'errors' => []
		];

		if ($context == 'backend-product')
			$sql = ['in', 'id', $data];
		else
			$sql = ['in', 'main_category_id', $this->getParentCategories($data)];

		$items = Product::find()
			->select(['id', 'name', 'currency_id', 'price', 'old_price'])
			->where($sql)
			->asArray()
			->all();

		foreach ($items as $item){
		 	if ($item['currency_id'] != $currencyId) {
		 		$report['skipped']++;
		 		continue;
		 	}

		 	// change prices
		 	$fError = false;
		 	$errorKey = '[' . $item['id'] . '] ' . $item['name'];
		 	$calcPrice = $item['price'];
			$calcOldPrice = $item['old_price'];

		 	if ($type == 'normal') {
			 	// price
			 	if ($selectedField == 'price' || $selectedField == 'all') {
			 		$calcPrice = $calculator($calcPrice, $value);
			 		if (!$this->checkAndRound($calcPrice)) {
			 			$fError = true;
			 			$report['errors'][$errorKey][Yii::t('app', 'Price')] = Yii::t('app', 'Сalculated value is less than zero');
				 	}
			 	}
			 	// old price
			 	if ($selectedField == 'old_price' || $selectedField == 'all') {
			 		$calcOldPrice = $calculator($calcOldPrice, $value);
			 		if (!$this->checkAndRound($calcOldPrice)) {
			 			$fError = true;
			 			$report['errors'][$errorKey][Yii::t('app', 'Old Price')] = Yii::t('app', 'Сalculated value is less than zero');
				 	}
			 	}
			 } else { // type == relative
			 	if ($selectedField == 'price') {
			 		$calcOldPrice = $calculator($calcPrice, $value);
			 		if (!$this->checkAndRound($calcOldPrice)) {
			 			$fError = true;
			 			$report['errors'][$errorKey][Yii::t('app', 'Old Price')] = Yii::t('app', 'Сalculated value is less than zero');
				 	}
			 	} else {
			 		$calcPrice = $calculator($calcOldPrice, $value);
			 		if (!$this->checkAndRound($calcPrice)) {
			 			$fError = true;
			 			$report['errors'][$errorKey][Yii::t('app', 'Price')] = Yii::t('app', 'Сalculated value is less than zero');
				 	}
				}
			 }
		 	
		 	$report['all']++;
		 	if ($fError) {
		 		$report['error']++;
		 	} else {
		 		$report['success']++;
	 			Product::updateAll(
		 			['price' => $calcPrice, 'old_price' => $calcOldPrice],
		 			['id' => $item['id']]
		 		);
		 	}
		}

		return $report;
	}
}