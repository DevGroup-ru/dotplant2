<?php
namespace app\modules\shop\actions;

use Yii;
use yii\base\Action;
use yii\web\Response;
use app\modules\shop\models\Category;
use app\modules\shop\models\Currency;
use app\modules\shop\models\Product;

class BatchEditPriceAction extends Action
{
    const BEP_CONTEXT_PRODUCT = 'backend-product';
    const BEP_KIND_FIXED = 'fixed';
    const BEP_TYPE_NORMAL = 'normal';
    const BEP_FIELD_PRICE = 'price';
    const BEP_FIELD_OLDPRICE = 'old_price';
    const BEP_FIELD_ALL = 'all';
    const BEP_INCREMENT = 'inc';

    public $prm = [];
    
    public function run()
    {
        if (false === Yii::$app->request->isAjax) {
            throw new NotFoundHttpException();
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        Yii::$app->log->flushInterval = 100;
        Yii::$app->log->traceLevel = 0;
        Yii::$app->log->targets = [
            new yii\log\FileTarget(
                [
                    'levels' => ['error'],
                    'exportInterval' => 100
                ]
            )
        ];

        $this->prm = [
            'is_child_inc' => Yii::$app->request->post('is_child_inc'),
            'kind' => Yii::$app->request->post('kind'),
            'operation' => Yii::$app->request->post('operation'),
            'is_round' => Yii::$app->request->post('is_round'),
            'round_val' => Yii::$app->request->post('round_val'),
            'sel_field' => Yii::$app->request->post('apply_for'),
            'value' => Yii::$app->request->post('value'),
            'type' => Yii::$app->request->post('type'),
            'currency_id' => Yii::$app->request->post('currency_id'),
        ];

        return $this->editPrices(
            Yii::$app->request->post('items', []),
            Yii::$app->request->post('context', '')
        );
    }

    /**
     * Gets products from selected categories
     * @param $list int[]
     * @return int[]
     */
    protected function getParentCategories($list)
    {
        $count = count($list);

        // read child cats
        if ($this->prm['is_child_inc']) {
            for ($i = 0; $i < $count; $i++) {
                $cats = Category::getByParentId($list[$i]);
                foreach ($cats as $category) {
                    $list[] = $category->id;
                    $count ++;
                }
            }
        }
        
        return $list;
    }

    /**
     * Set the algorithm of calculation
     * @return float function (float, float)
     */
    protected function getCalculator()
    {
        if ($this->prm['kind'] == self::BEP_KIND_FIXED) { // fixed value
            if ($this->prm['operation'] == self::BEP_INCREMENT) {
                $calculator = function ($subj, $value) {
                    return $subj + $value;
                };
            } else {
                 $calculator = function ($subj, $value) {
                     return $subj - $value;
                 };
            }
        } else { // percent value
            if ($this->prm['operation'] == self::BEP_INCREMENT) {
                 $calculator = function ($subj, $value) {
                     return  $subj * (1 + $value / 100);
                 };
            } else {
                $calculator = function ($subj, $value) {
                     return  $subj * (1 - $value / 100);
                };
            }
        }

         return $calculator;
    }

    /**
     * To compare with zero and round
     * @param &$price float - $price can be rounded
     * @return bool
     */
    protected function checkAndRound(&$price)
    {
        if ($price >= 0) {
            if ($this->prm['is_round']) {
                $price = round(
                    $price,
                    $this->prm['round_val']
                );
            }
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
    protected function editPrices($data, $context)
    {
        $calculator = $this->getCalculator();
        $report = [
            'all' => 0,
            'success' => 0,
            'error' => 0,
            'skipped' => 0,
            'errors' => []
        ];

        if ($context == self::BEP_CONTEXT_PRODUCT) {
            $condition = ['in', 'id', $data];
        } else {
            $condition = ['in', 'main_category_id', $this->getParentCategories($data)];
        }

        $items = Product::find()
            ->select(['id', 'name', 'currency_id', 'price', 'old_price'])
            ->where($condition)
            ->asArray()
            ->all();

        foreach ($items as $item) {
            $report['all']++;
            if ($item['currency_id'] != $this->prm['currency_id']) {
                $report['skipped']++;
                continue;
            }

            // change prices
            $fError = false;
            $errorKey = '[' . $item['id'] . '] ' . $item['name'];
            $calcPrice = $item['price'];
            $calcOldPrice = $item['old_price'];

            if ($this->prm['type'] == self::BEP_TYPE_NORMAL) {
                // price
                if ($this->prm['sel_field'] == self::BEP_FIELD_PRICE
                    || $this->prm['sel_field'] == self::BEP_FIELD_ALL
                ) {
                    $calcPrice = $calculator($calcPrice, $this->prm['value']);
                    if (!$this->checkAndRound($calcPrice)) {
                        $fError = true;
                        $report['errors'][$errorKey][Yii::t('app', 'Price')] = Yii::t('app', 'Сalculated value is less than zero');
                    }
                }
                // old price
                if ($this->prm['sel_field'] == self::BEP_FIELD_OLDPRICE
                    || $this->prm['sel_field'] == self::BEP_FIELD_ALL
                ) {
                    $calcOldPrice = $calculator($calcOldPrice, $this->prm['value']);
                    if (!$this->checkAndRound($calcOldPrice)) {
                        $fError = true;
                        $report['errors'][$errorKey][Yii::t('app', 'Old Price')] = Yii::t('app', 'Сalculated value is less than zero');
                    }
                }
            } else { // type == relative
                if ($this->prm['sel_field'] == self::BEP_FIELD_PRICE) {
                    $calcOldPrice = $calculator($calcPrice, $this->prm['value']);
                    if (!$this->checkAndRound($calcOldPrice)) {
                        $fError = true;
                        $report['errors'][$errorKey][Yii::t('app', 'Old Price')] = Yii::t('app', 'Сalculated value is less than zero');
                    }
                } else {
                    $calcPrice = $calculator($calcOldPrice, $this->prm['value']);
                    if (!$this->checkAndRound($calcPrice)) {
                        $fError = true;
                        $report['errors'][$errorKey][Yii::t('app', 'Price')] = Yii::t('app', 'Сalculated value is less than zero');
                    }
                }
            }

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
