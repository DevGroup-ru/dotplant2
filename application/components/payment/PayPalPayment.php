<?php
namespace app\components\payment;

use app\modules\shop\helpers\CurrencyHelper;
use app\modules\shop\helpers\PriceHelper;
use app\modules\shop\models\Currency;
use app\modules\shop\models\Order;
use app\modules\shop\models\OrderItem;
use app\modules\shop\models\OrderTransaction;
use app\modules\shop\models\Product;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;

class PayPalPayment extends AbstractPayment
{
    /**
     * @var string $clientId
     * @var string $clientSecret
     * @var bool $sandbox
     * @var Currency|string $currency
     */
    public $clientId = null;
    public $clientSecret = null;
    public $sandbox = false;
    public $currency = null;
    public $transactionDescription = '';

    private $apiContext = null;

    const STATE_APPROVED = 'approved';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->apiContext = new ApiContext(new OAuthTokenCredential($this->clientId, $this->clientSecret));
        $this->apiContext->setConfig([
            'mode' => true === $this->sandbox ? 'sandbox' : 'live',
            'log.LogEnabled' => false,
            'cache.enabled' => true,
            'cache.FileName' => \Yii::getAlias('@runtime/paypal.cache'),
        ]);

        if (false === $this->currency instanceof Currency) {
            $this->currency = CurrencyHelper::findCurrencyByIso($this->currency);
        }
    }

    /**
     * @inheritdoc
     */
    public function content()
    {
        /** @var Order $order */
        $order = $this->order;
        $order->calculate();

        $payer = (new Payer())->setPaymentMethod('paypal');

        $priceSubTotal = 0;
        /** @var ItemList $itemList */
        $itemList = array_reduce($order->items, function($result, $item) use (&$priceSubTotal) {
            /** @var OrderItem $item */
            /** @var Product $product */
            $product = $item->product;
            $price = CurrencyHelper::convertFromMainCurrency($item->price_per_pcs, $this->currency);
            $priceSubTotal = $priceSubTotal + ($price * $item->quantity);

            /** @var ItemList $result */
            return $result->addItem(
                (new Item())
                    ->setName($product->name)
                    ->setCurrency($this->currency->iso_code)
                    ->setPrice($price)
                    ->setQuantity($item->quantity)
                    ->setUrl(Url::toRoute([
                        '@product',
                        'model' => $product,
                        'category_group_id' => $product->category->category_group_id
                    ], true))
            );
        }, new ItemList());

        $priceTotal = CurrencyHelper::convertFromMainCurrency($order->total_price, $this->currency);

        $details = (new Details())
            ->setShipping($priceTotal - $priceSubTotal)
            ->setSubtotal($priceSubTotal)
            ->setTax(0);

        $amount = (new Amount())
            ->setCurrency($this->currency->iso_code)
            ->setTotal($priceTotal)
            ->setDetails($details);

        $transaction = (new Transaction())
            ->setAmount($amount)
            ->setItemList($itemList)
            ->setDescription($this->transactionDescription)
            ->setInvoiceNumber($this->transaction->id);

        $urls = (new RedirectUrls())
            ->setReturnUrl($this->createResultUrl(['id' => $this->order->payment_type_id]))
            ->setCancelUrl($this->createFailUrl());

        $payment = (new Payment())
            ->setIntent('sale')
            ->setPayer($payer)
            ->setTransactions([$transaction])
            ->setRedirectUrls($urls);

        $link = null;
        try {
            $link = $payment->create($this->apiContext)->getApprovalLink();
        } catch (\Exception $e) {
            $link = null;
        }

        return $this->render('paypal', [
            'order' => $order,
            'transaction' => $this->transaction,
            'approvalLink' => $link,
        ]);
    }

    /**
     * @inheritdoc
     * @throws BadRequestHttpException
     */
    public function checkResult($hash = '')
    {
        if (false === $this->executePayment()) {
            throw new BadRequestHttpException();
        }

        return $this->redirect(
            $this->createSuccessUrl()
        );
    }

    /**
     * @inheritdoc
     * @throws BadRequestHttpException
     */
    public function customCheck()
    {
        if (false === $this->executePayment()) {
            throw new BadRequestHttpException();
        }

        return $this->redirect(
            $this->createSuccessUrl()
        );
    }

    /**
     * @return bool
     */
    private function executePayment()
    {
        $result = false;
        try {
            $request = \Yii::$app->request;

            $result = Payment::get($request->get('paymentId'), $this->apiContext)
                ->execute(
                    (new PaymentExecution())->setPayerId($request->get('PayerID')),
                    $this->apiContext
                );

            $status = static::STATE_APPROVED === $result->getState()
                ? OrderTransaction::TRANSACTION_SUCCESS
                : OrderTransaction::TRANSACTION_ERROR;

            $detail = Payment::get($request->get('paymentId'), $this->apiContext);

            foreach ($detail->getTransactions() as $transaction) {
                /** @var Transaction $transaction */
                if (null !== $orderTransaction = OrderTransaction::findOne(['id' => $transaction->getInvoiceNumber()])) {
                    /** @var OrderTransaction $orderTransaction */
                    $orderTransaction->updateStatus($status);
                    $this->transaction = $orderTransaction;

                    $result = OrderTransaction::TRANSACTION_SUCCESS === $status ? true : false;
                }
            }
        } catch (\Exception $e) {
        }

        return $result;
    }
}
