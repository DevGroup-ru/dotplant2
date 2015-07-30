<?php
namespace app\components\payment;

use app\modules\shop\models\OrderTransaction;
use SimpleXMLElement;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\HttpException;

class PlatronPayment extends AbstractPayment
{
    public $merchantId;
    public $secretKey;
    public $strCurrency = 'RUR';
    public $merchantUrl = 'www.platron.ru';
    public $merchantScriptName = 'payment.php';

    /**
     * @return string
     */
    public function content()
    {
        $arrReq = [
            'pg_merchant_id' => $this->merchantId,
            'pg_order_id' => $this->transaction->id,
            'pg_currency' => $this->strCurrency,
            'pg_amount' => $this->transaction->total_sum,
            'pg_lifetime' => 3600 * 24,
            'pg_description' => \Yii::t('app', 'Payment of order #{orderId}', ['orderId' => $this->order->id]),
            'pg_language' => Yii::$app->language == 'ru' ? 'ru' : 'en',
            'pg_result_url' => $this->createResultUrl([
                'id' => $this->order->payment_type_id,
                'transactionId' => $this->transaction->id,
            ]),
            'pg_success_url' => $this->createSuccessUrl([
                'id' => $this->order->id,
            ]),
            'pg_failure_url' => $this->createFailUrl([
                'id' => $this->order->id,
            ]),
            'pg_salt' => Yii::$app->security->generateRandomString(8),
        ];

        if (!Yii::$app->user->isGuest) {
            $arrReq['pg_user_email'] = Yii::$app->user->identity->email;
            $arrReq['pg_user_contact_email'] = Yii::$app->user->identity->email;
        }

        $arrReq['pg_sig'] = self::make($this->merchantScriptName, $arrReq, $this->secretKey);
        $query = http_build_query($arrReq);
        return $this->render(
            'platron',
            [
                'order' => $this->order,
                'url' => 'https://' . $this->merchantUrl . '/' . $this->merchantScriptName . '?' . $query,
            ]
        );
    }

    /**
     * Creates a signature
     *
     * @param string $strScriptName script name
     * @param array $arrParams associative array of parameters for the signature
     * @param string $strSecretKey
     * @return string
     */
    public static function make($strScriptName, $arrParams, $strSecretKey)
    {
        $arrFlatParams = self::makeFlatParamsArray($arrParams);
        return md5(self::makeSigStr($strScriptName, $arrFlatParams, $strSecretKey));
    }

    /**
     * @param $arrParams
     * @param string $parent_name
     * @return array
     */
    private static function makeFlatParamsArray($arrParams, $parent_name = '')
    {
        $arrFlatParams = array();
        $i = 0;
        foreach ($arrParams as $key => $val) {

            $i++;
            if ('pg_sig' == $key) {
                continue;
            }

            /**
             * The name of the form do tag001subtag001
             * To be able to sort and then properly nested nodes do not get confused when sorting
             */
            $name = $parent_name . $key . sprintf('%03d', $i);
            if (is_array($val)) {
                $arrFlatParams = ArrayHelper::merge($arrFlatParams, self::makeFlatParamsArray($val, $name));
                continue;
            }
            $arrFlatParams += array($name => (string)$val);
        }
        return $arrFlatParams;
    }

    /**
     * @param $strScriptName
     * @param array $arrParams
     * @param $strSecretKey
     * @return string
     */
    private static function makeSigStr($strScriptName, array $arrParams, $strSecretKey)
    {
        unset($arrParams['pg_sig']);

        ksort($arrParams);
        array_unshift($arrParams, $strScriptName);
        array_push($arrParams, $strSecretKey);
        return join(';', $arrParams);
    }

    /**
     * @param string $hash
     * @return void
     * @throws HttpException
     * @throws \yii\web\NotFoundHttpException
     */
    public function checkResult($hash = '')
    {
        $arrParams = Yii::$app->request->get();
        unset($arrParams['id']);
        $thisScriptName = self::getOurScriptName();
        if (!self::check($arrParams['pg_sig'], $thisScriptName, $arrParams, $this->secretKey)) {
            throw new HttpException(500, 'Bad signature');
        }
        $order_id = $arrParams['pg_order_id'];
        $transaction = $this->loadTransaction($order_id);
        if ($arrParams['pg_result'] == 1) {

            $transaction->result_data = Json::encode($arrParams);
            $transaction->status = OrderTransaction::TRANSACTION_SUCCESS;
            $transaction->save(true, ['status', 'result_data']);
        } else {
            $transaction->status = OrderTransaction::TRANSACTION_ERROR;
            $transaction->result_data = Json::encode($arrParams);
            $transaction->save(true, ['status', 'result_data']);
        }


        /*
         * Form a response XML
         */
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><response/>');
        $xml->addChild('pg_salt', $arrParams['pg_salt']);
        $xml->addChild('pg_status', 'ok');
        $xml->addChild('pg_description', "Оплата принята");
        $xml->addChild('pg_sig', self::makeXML($thisScriptName, $xml, $this->secretKey));
        header('Content-type: text/xml');
        echo $xml->asXML();
    }

    /**
     * Get name of currently executed script (need to check signature of incoming message using self::check)
     *
     * @return string
     */
    public static function getOurScriptName()
    {

        return self::getScriptNameFromUrl(\Yii::$app->request->url);
    }

    /**
     * Get script name from URL (for use as parameter in self::make, self::check, etc.)
     *
     * @param string $url
     * @return string
     */
    public static function getScriptNameFromUrl($url)
    {
        $path = parse_url($url, PHP_URL_PATH);
        $len = strlen($path);
        if ($len == 0 || '/' == $path{$len - 1}) {
            return "";
        }
        return basename($path);
    }
    /********************** singing XML ***********************/

    /**
     * Verifies the signature
     *
     * @param string $signature
     * @param string $strScriptName script name
     * @param array $arrParams associative array of parameters for the signature
     * @param string $strSecretKey
     * @return bool
     */
    public static function check($signature, $strScriptName, $arrParams, $strSecretKey)
    {
        return (string)$signature === self::make($strScriptName, $arrParams, $strSecretKey);
    }

    /**
     * make the signature for XML
     *
     * @param string|SimpleXMLElement $xml
     * @param string $strSecretKey
     * @return string
     */
    public static function makeXML($strScriptName, $xml, $strSecretKey)
    {
        $arrFlatParams = self::makeFlatParamsXML($xml);
        return self::make($strScriptName, $arrFlatParams, $strSecretKey);
    }

    /**
     * Returns flat array of XML params
     *
     * @param (string|SimpleXMLElement) $xml
     * @param string $parent_name paren name
     * @return array
     */
    private static function makeFlatParamsXML($xml, $parent_name = '')
    {
        if (!$xml instanceof SimpleXMLElement) {
            $xml = new SimpleXMLElement($xml);
        }
        $arrParams = array();
        $i = 0;
        foreach ($xml->children() as $tag) {

            $i++;
            if ('pg_sig' == $tag->getName()) {
                continue;
            }

            /**
             * The name of the form do tag001subtag001
             * To be able to sort and then properly nested nodes do not get confused when sorting
             */
            $name = $parent_name . $tag->getName() . sprintf('%03d', $i);
            if ($tag->children()->count() > 0) {
                $arrParams = array_merge($arrParams, self::makeFlatParamsXML($tag, $name));
                continue;
            }
            $arrParams += array($name => (string)$tag);
        }
        return $arrParams;
    }
}