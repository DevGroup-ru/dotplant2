<?php
namespace app\modules\shop\widgets\charts;

use app\modules\user\models\User;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Connection;
use yii\helpers\Json;
use app\modules\shop\models\Currency;
use yii\base\Widget;
use app\modules\shop\models\Order;
use app\modules\shop\models\OrderTransaction;
use app\modules\shop\helpers\CurrencyHelper;
use app\modules\shop\widgets\charts\assets\SalesChartsAsset;

class SalesChartsWidget extends Widget
{
    /**
     * @property string $period
     * @property string $viewFile
     * @property string $statsDate
     * @property string $userDate
     * @property string $dateFormat
     * @property Currency|string $currency Object or ISO-4217 code
     * @property string|null $jsTooltip
     */
    public $period = self::LAST_MONTH;
    public $viewFile = 'dashboard';
    public $statsDate = self::LAST_YEAR;
    public $userDate = self::LAST_MONTH;
    public $dateFormat = "%y-%0m-%0d";
    public $currency = null;
    public $jsTooltip = null;

    protected $startDate;

    /** @var   ActiveRecord */
    static private $ordersQuery;

    const LAST_YEAR = 'year';
    const LAST_MONTH = 'month';
    const LAST_WEEK = 'week';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        SalesChartsAsset::register($this->view);

        if (false === $this->currency instanceof Currency) {
            $this->currency = CurrencyHelper::findCurrencyByIso($this->currency);
        }

        $this->startDate = $this->getPeriodTs($this->period);
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $cacheKey = implode(':', [
            $this->className(),
            $this->viewFile,
        ]);

        if (false !== $cache = Yii::$app->cache->get($cacheKey)) {
            return $cache;
        }

        $salesChart = $this->prepareSalesChart();
        $salesChart = [
            'header' => $salesChart['header'],
            'js' => 'window.DotplantSalesCharts = ' . Json::encode($salesChart) . ';',
        ];
        $result = $this->render($this->viewFile, [
            'salesChart' => $salesChart,
            'statistics' => $this->prepareStatistics(),
            'userData' => $this->prepareUserData(),
        ]);

        return $result;
    }

    /**
     * @return array Where key = Shop Title, value = array with Shop Statistics
     */
    protected function prepareStatistics()
    {
        return [
            Yii::t('app', 'Main shop') => $this->getOrderStatistics(Yii::$app->db, $this->currency),
        ];
    }

    /**
     * @return string
     */
    protected function prepareSalesChart()
    {
        $tooltip = null !== $this->jsTooltip
            ? $this->jsTooltip
            : Yii::t('app', 'Your sales for <b>{date}</b> was <span>{price} {code}</span>', [
                'date' => '%x',
                'price' => '%y',
                'code' => CurrencyHelper::getCurrencySymbol($this->currency),
            ]);

        $shops = $this->getOrderSalesChart(Yii::$app->db, $this->currency);
        $periodTotalSold = $shops['totalSum'];
        $shops = [['label' => Yii::t('app', 'Main shop'), 'data' => $shops['orders'],],];

        $header = Yii::t('app', 'Last {period} sold for:', ['period' => Yii::t('app', $this->period)])
            . ' ' . $this->currency->format($periodTotalSold);

        $data = [
            'tooltip' => $tooltip,
            'header' => $header,
            'dateFormat' => $this->dateFormat,
            'shops' => $shops,
        ];
        return $data;
    }

    /**
     * @return array
     */
    protected function prepareUserData()
    {
        return [
            Yii::t('app', 'Main shop') => $this->getUserStatistics(Yii::$app->db),
        ];
    }

    /**
     * @param string $period
     * @return int
     */
    protected function getPeriodTs($period)
    {
        return strtotime("-1 " . $period);
    }

    /**
     * @param OrderTransaction $transaction
     * @param Currency $currency
     * @return float|int
     */
    protected function getTotalSumOrderTransaction(OrderTransaction $transaction, Currency $currency)
    {
        return CurrencyHelper::convertFromMainCurrency($transaction->total_sum, $currency);
    }

    /**
     * @param Connection $db
     * @param Currency $currency
     * @return array
     */
    protected function getOrderStatistics(Connection $db, Currency $currency)
    {
        /** @var ActiveQuery $query */
        $query = OrderTransaction::find()
            ->where(['status' => OrderTransaction::TRANSACTION_SUCCESS])
            ->orderBy(['end_date' => SORT_ASC]);

        $statistics = [];
        $_totalOrders = 0;
        $_totalSales = 0;
        foreach ($query->each(100, $db) as $transaction) {
            $_totalOrders++;
            $_totalSales += $this->getTotalSumOrderTransaction($transaction, $currency);
        }
        $statistics = array_merge(
            $statistics,
            [
                Yii::t('app', 'Total orders') => $_totalOrders,
                Yii::t('app', 'Total sales') => $currency->format($_totalSales),
            ]
        );

        $query = $query->andWhere(['>=', 'UNIX_TIMESTAMP(`end_date`)', self::getPeriodTs($this->statsDate)]);
        $_totalOrders = 0;
        $_totalSales = 0;
        foreach ($query->each(100, $db) as $transaction) {
            $_totalOrders++;
            $_totalSales += $this->getTotalSumOrderTransaction($transaction, $currency);
        }
        $statistics = array_merge(
            $statistics,
            [
                Yii::t('app', 'Total orders') . ' ' . Yii::t('app', $this->statsDate) => $_totalOrders,
                Yii::t('app', 'Total sales') . ' ' . Yii::t('app', $this->statsDate) => $currency->format($_totalSales),
            ]
        );

        return $statistics;
    }

    /**
     * @param Connection $db
     * @param Currency $currency
     * @return array
     */
    protected function getOrderSalesChart(Connection $db, Currency $currency)
    {
        /** @var ActiveQuery $query */
        $query = OrderTransaction::find()
            ->where(['status' => OrderTransaction::TRANSACTION_SUCCESS])
            ->andWhere(['>=', 'UNIX_TIMESTAMP(`end_date`)', $this->startDate])
            ->orderBy(['end_date' => SORT_ASC]);

        $orders = [];
        $totalSum = 0;
        /** @var OrderTransaction $transaction */
        foreach ($query->each(100, $db) as $transaction) {
            $t = $this->getTotalSumOrderTransaction($transaction, $currency);
            $totalSum += $t;
            $orders[] = [strtotime($transaction->end_date) * 1000, $t];
        }

        return [
            'totalSum' => $totalSum,
            'orders' => $orders,
        ];
    }

    /**
     * @param Connection $db
     * @return array
     */
    protected function getUserStatistics(Connection $db)
    {
        $usersIds = User::find()->select('id')->column($db);
        $activeUsers = Order::find()
            ->select('user_id')
            ->innerJoin(
                OrderTransaction::tableName(),
                Order::tableName() . '.id = ' . OrderTransaction::tableName() . '.order_id'
            )
            ->where([
                'status' => 5,
                'user_id' => $usersIds
            ])
            ->count('*', $db);
        $lastRegistered = User::find()
            ->where(['>=', 'create_time', $this->getPeriodTs($this->userDate)])
            ->count('*', $db);
        $userData = [
            Yii::t('app', 'Registered customers') => count($usersIds),
            Yii::t('app', 'Active registered customers') => $activeUsers,
            Yii::t('app', 'New registered customers for last {period}', ['period' => Yii::t('app', $this->userDate)]) => $lastRegistered,
        ];
        return $userData;
    }
}
