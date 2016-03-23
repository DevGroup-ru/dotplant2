<?php
namespace app\modules\shop\actions;

use Yii;
use yii\base\Action;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use app\modules\shop\models\Category;
use app\modules\shop\models\Product;

class BatchEditPriceAction extends Action
{
    const BEP_CONTEXT_PRODUCT = 'backend-product';
    const BEP_KIND_FIXED = 'fixed';
    const BEP_KIND_PERCENT = 'percentage';
    const BEP_TYPE_NORMAL = 'normal';
    const BEP_TYPE_RELATIVE = 'relative';
    const BEP_FIELD_PRICE = 'price';
    const BEP_FIELD_OLDPRICE = 'old_price';
    const BEP_FIELD_ALL = 'all';
    const BEP_INCREMENT = 'inc';
    const BEP_DECREMENT = 'dec';

    public $params = [];
    
    public function run()
    {
        if (false === Yii::$app->request->isAjax) {
            throw new NotFoundHttpException();
        }
        Yii::$app->response->format = Response::FORMAT_JSON;

        $this->params = [
            'is_child_inc' => Yii::$app->request->post('is_child_inc', false),
            'kind' => Yii::$app->request->post('kind', ''),
            'operation' => Yii::$app->request->post('operation', ''),
            'is_round' => Yii::$app->request->post('is_round', true),
            'round_val' => intval(Yii::$app->request->post('round_val', 2)),
            'sel_field' => Yii::$app->request->post('apply_for', ''),
            'value' => floatval(Yii::$app->request->post('value', 0)),
            'type' => Yii::$app->request->post('type', ''),
            'currency_id' => intval(Yii::$app->request->post('currency_id', 0)),
            'context' => Yii::$app->request->post('context', ''),
            'items' => Yii::$app->request->post('items', [])
        ];

        return $this->editPrices();
    }

    /**
     * Get list of child categories
     * @param $list int[]
     * @return int[]
     */
    protected function getParentCategories($list)
    {
        if ($this->params['is_child_inc']) {
            $count = count($list);
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
     * Get parameters to build queries
     * @return mixed[]
     */
    protected function getSqlStatements()
    {
        $updateRule = [];
        $condition = [];
        $args = [];

        $conditionFormat = '%s %s >= 0';
        if ($this->params['is_round']) {
            $updateFormat = 'ROUND(%s %s, :round)';
        } else {
            $updateFormat = '%s %s';
        }

        // operation
        if ($this->params['operation'] == self::BEP_INCREMENT) {
            $operation = '+';
        } elseif ($this->params['operation'] == self::BEP_DECREMENT) {
            $operation = '-';
        }
        if ($this->params['kind'] == self::BEP_KIND_FIXED) {
            $args['operation'] = $operation . ' :val';
        } elseif ($this->params['kind'] == self::BEP_KIND_PERCENT) {
            $args['operation'] = " * (1 {$operation} :val / 100)";
        }

        // price | old_price | both of them
        if ($this->params['type'] == self::BEP_TYPE_NORMAL) {
            if ($this->params['sel_field'] == self::BEP_FIELD_PRICE
                || $this->params['sel_field'] == self::BEP_FIELD_ALL
            ) {
                $args['field_to'] = self::BEP_FIELD_PRICE;
                $args['field_from'] = self::BEP_FIELD_PRICE;
            }
            if ($this->params['sel_field'] == self::BEP_FIELD_OLDPRICE) {
                $args['field_to'] = self::BEP_FIELD_OLDPRICE;
                $args['field_from'] = self::BEP_FIELD_OLDPRICE;
            }

            if ($this->params['sel_field'] == self::BEP_FIELD_ALL) {
                $updateRule[$args['field_to']] = new \yii\db\Expression(
                    sprintf(
                        $updateFormat,
                        $args['field_from'],
                        $args['operation']
                    )
                );

                $condition[] = $args['field_from'] . ' ' . $args['operation'] . '>= 0';
                $args['field_to'] = self::BEP_FIELD_OLDPRICE;
                $args['field_from'] = self::BEP_FIELD_OLDPRICE;
            }
        } elseif ($this->params['type'] == self::BEP_TYPE_RELATIVE) {
            if ($this->params['sel_field'] == self::BEP_FIELD_PRICE) {
                $args['field_to'] = self::BEP_FIELD_OLDPRICE;
                $args['field_from'] = self::BEP_FIELD_PRICE;
            }
            if ($this->params['sel_field'] == self::BEP_FIELD_OLDPRICE) {
                $args['field_to'] = self::BEP_FIELD_PRICE;
                $args['field_from'] = self::BEP_FIELD_OLDPRICE;
            }
        }

        $updateRule[$args['field_to']] = new \yii\db\Expression(
            sprintf(
                $updateFormat,
                $args['field_from'],
                $args['operation']
            )
        );

        $condition[] = $args['field_from'] . ' ' . $args['operation'] . ' >= 0';
        $condition[] = 'currency_id = :currency';
        if ($this->params['context'] == self::BEP_CONTEXT_PRODUCT) {
            $data = implode(',', $this->params['items']);
            $condition['for_count'] = 'id IN (' . $data . ')';
        } else {
            $data = implode(',', $this->getParentCategories($this->params['items']));
            $condition['for_count'] = 'main_category_id IN (' . $data . ')';
        }

        return [
            'rule' => $updateRule,
            'condition' => new \yii\db\Expression(implode(' AND ', $condition)),
            'condition_for_count' => new \yii\db\Expression($condition['for_count'])
        ];
    }

    /**
     * Editing of prices
     * @return int[]
     */
    protected function editPrices()
    {
        $sqlStatements = $this->getSqlStatements();

        $result['all'] = Product::find()
            ->where($sqlStatements['condition_for_count'])
            ->count();

        $result['success'] = Product::updateAll(
            $sqlStatements['rule'],
            $sqlStatements['condition'],
            [
                ':val' => $this->params['value'],
                ':round' => $this->params['round_val'],
                ':currency' => $this->params['currency_id']
            ]
        );

        return $result;
    }
}
