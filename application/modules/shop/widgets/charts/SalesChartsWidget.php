<?php

namespace app\modules\shop\widgets\charts;

use app\modules\user\models\User;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Json;
use app\modules\shop\models\Currency;
use yii\base\Widget;
use app\modules\shop\models\Order;
use app\modules\shop\models\OrderTransaction;
use app\modules\shop\helpers\CurrencyHelper;
use app\modules\shop\widgets\charts\assets\SalesChartsAsset;

class SalesChartsWidget extends Widget
{
    public $period = self::LAST_MONTH;
    public $viewFile = 'dashboard';
    public $statsDate = self::LAST_YEAR;
    public $userDate = self::LAST_MONTH;
    public $dateFormat = "%y-%0m-%0d";
    public $currencyCode = 'GBP';

    private $startDate;
    private $periodTotalSold = 0;

    /** @var   ActiveRecord */
    private static $ordersQuery;
    /** @var  Currency */
    private $currency;

    const LAST_YEAR = 'year';
    const LAST_MONTH = 'month';
    const LAST_WEEK = 'week';

    public function init()
    {
        if (null === $this->currency = CurrencyHelper::findCurrencyByIso($this->currencyCode)) {
            $this->currency = CurrencyHelper::getMainCurrency();
        }
        $this->startDate = self::getPeriodTs($this->period);
        self::$ordersQuery = OrderTransaction::getDb()->cache(function ($db) {
            return OrderTransaction::find()
                ->select('UNIX_TIMESTAMP(`end_date`) as ts, status, end_date, total_sum, order_id')
                ->where(['status' => 5])
                ->asArray()
                ->orderBy(['end_date' => SORT_ASC]);
        });
        SalesChartsAsset::register($this->view);
        return parent::init();
    }

    public function run()
    {
        return $this->render(
            $this->viewFile,
            [
                'salesChart' => $this->prepareSalesChart(),
                'statistics' => $this->prepareStatistics(),
                'userData' => $this->prepareUserData(),
            ]
        );
    }

    private function prepareStatistics()
    {
        $statistics = [];
        $totalOrders = self::$ordersQuery->all();
        $statistics = array_merge($statistics, $this->prepareOrders($totalOrders));
        $ordersLastYear = self::$ordersQuery
            ->andWhere(['>=', 'UNIX_TIMESTAMP(`end_date`)', self::getPeriodTs($this->statsDate)])
            ->all();
        $statistics = array_merge($statistics, $this->prepareOrders($ordersLastYear, $this->statsDate));
        return $statistics;
    }

    private function prepareSalesChart()
    {
        $jsOrders = [];
        $orders = self::$ordersQuery->andWhere(['>=', 'UNIX_TIMESTAMP(`end_date`)', $this->startDate])->all();
        foreach ($orders as $order) {
            /** @var  $order OrderTransaction */
            $convertedPrice = CurrencyHelper::convertCurrencies($order['total_sum'], CurrencyHelper::getMainCurrency(), $this->currency);
            $jsOrders[] = [strtotime($order['end_date']) * 1000, $convertedPrice];
            $this->periodTotalSold += $convertedPrice;
        }
        $jsOrders = Json::encode($jsOrders);
        $chartData = [
            'salesHeader' => Yii::t('app', 'Last {period} sold for:', ['period' => Yii::t('app', $this->period)])
                . ' ' . $this->currency->format($this->periodTotalSold),
            'tooltipTpl' => Yii::t('app', 'Your sales for <b>{date}</b> was <span>{price} {code}</span>', [
                'date' => '%x',
                'price' => '%y',
                'code' => $this->getSign(),
            ]),
            'dateFormat' => $this->dateFormat,
            'jsOrders' => $jsOrders,
        ];
        return $chartData;
    }

    private function prepareUserData()
    {
        $usersIds = User::find()->select('id')->column();
        $activeUsers = Order::getDb()->cache(function ($db) use ($usersIds){
           return Order::find()
               ->select('user_id')
               ->innerJoin(
                   OrderTransaction::tableName(),
                   Order::tableName() . '.id = ' . OrderTransaction::tableName() . '.order_id'
               )
               ->where([
                   'status' => 5,
                   'user_id' => $usersIds
               ])
               ->count();
        });
        $lastRegistered = User::getDb()->cache(function ($db) {
           return User::find()
               ->where(['>=', 'create_time', $this->getPeriodTs($this->userDate)])
               ->count();
        });
        $userData = [
            Yii::t('app', 'Registered customers') => count($usersIds),
            Yii::t('app', 'Active registered customers') => $activeUsers,
            Yii::t('app', 'New registered customers for last {period}', ['period' => Yii::t('app', $this->userDate)]) => $lastRegistered,
        ];
        return $userData;
    }

    private function getSign()
    {
        return preg_replace('%[\d\s,]%i', '', $this->currency->format(0));
    }

    private static function getPeriodTs($period)
    {
        return strtotime("-1 " . $period);
    }

    private function prepareOrders($orders, $period = '')
    {
        $output = [];
        if (false === empty($orders)) {
            $ordersKey = empty($period) ? Yii::t('app', 'Total orders') : Yii::t('app', 'Total orders this') . ' ' . Yii::t('app', $period);
            $salesKey = empty($period) ? Yii::t('app', 'Total sales') : Yii::t('app', 'Total sales this') . ' ' . Yii::t('app', $period);
            $output[$ordersKey] = count($orders);
            $prices = array_column($orders, 'total_sum');
            $output[$salesKey] = $this->currency->format(array_sum($prices));
        }
        return $output;
    }
}