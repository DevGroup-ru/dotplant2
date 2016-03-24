<?php


namespace app\modules\shop\data;


use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

class FilterPagination extends Pagination
{

    public function init()
    {
        parent::init();
        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();
            $get = Yii::$app->request->get();
            unset($post['_csrf']);
            $this->params = ArrayHelper::merge($this->params, $get, $post);
        }
    }

    public function getQueryParam($name, $defaultValue = null)
    {
        $value = parent::getQueryParam($name, $defaultValue);
        if ($value === $defaultValue) {
            $params = ArrayHelper::merge(Yii::$app->request->post(), Yii::$app->request->get());
            $value = ArrayHelper::getValue($params, $name, $defaultValue);
        }
        return $value;
    }

}