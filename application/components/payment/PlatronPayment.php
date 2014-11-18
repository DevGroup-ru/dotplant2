<?php
/**
 * Created by PhpStorm.
 * User: dmitriy
 * Date: 18.11.14
 * Time: 11:16
 */

namespace app\components\payment;
use app\models\OrderTransaction;
use yii\helpers\Url;
use SimpleXMLElement;
use yii\helpers\Json;

class PlatronPayment extends AbstractPayment {


    protected $merchant_id;
    protected $secret_key;
    protected $strCurrency = 'RUR';
    protected $merchantUrl = 'www.platron.ru/payment.php';


    public function content($order, $transaction)
    {


        $arrReq = [
            'pg_merchant_id' => $this->merchant_id,
            'pg_order_id' => $transaction->id,
            'pg_amount' => $transaction->total_sum,
            'pg_lifetime' => 3600*24,
            'pg_description' => \Yii::t('shop', 'Payment of order #{orderId}', ['orderId' => $order->id]),
            'pg_result_url' => Url::toRoute(['/cart/payment-result', 'id' => $transaction->payment_type_id], true),
            'pg_success_url' => Url::toRoute(['/cart/payment-success', 'id' => $order->id], true),
            'pg_failure_url' => Url::toRoute(['/cart/payment-error', 'id' => $order->id], true),
            'pg_salt' => rand(21,43433),

        ];

        $arrReq['pg_sig'] = self::make('payment.php', $arrReq, $this->secret_key);
        $query = http_build_query($arrReq);
        return $this->render(
            'platron',
            [
                'order' => $order,
                'url' => 'https://' .$this->merchantUrl. '?'.$query,
            ]
        );



    }

     public function checkResult()
     {

         $arrParams = $_GET;
         unset($arrParams['id']);

         $thisScriptName = self::getOurScriptName();
         if ( !self::check($arrParams['pg_sig'], $thisScriptName, $arrParams, $this->secret_key) )
             die("Bad signature");
         $order_id = $arrParams['pg_order_id'];
         $transaction = $this->loadTransaction($order_id);
         if ( $arrParams['pg_result'] == 1 ) {

             $transaction->result_data = Json::encode($arrParams);
             $transaction->status = OrderTransaction::TRANSACTION_SUCCESS;
             $transaction->save(true, ['status', 'result_data']);
         }
         else{
             $transaction->status = OrderTransaction::TRANSACTION_ERROR;
             $transaction->result_data = Json::encode($arrParams);
             $transaction->save(true, ['status', 'result_data']);
         }
         /*
          * Формируем ответный XML
          * (Это можно делать вручную, как в примере check.php, или используя SimpleXML, как в данном примере)
          */
         $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><response/>');
         $xml->addChild('pg_salt', $arrParams['pg_salt']); // в ответе необходимо указывать тот же pg_salt, что и в запросе
         $xml->addChild('pg_status', 'ok');
         $xml->addChild('pg_description', "Оплата принята");
         $xml->addChild('pg_sig', self::makeXML($thisScriptName, $xml, $this->secret_key));
         header('Content-type: text/xml');
         print $xml->asXML();
     }
    /**
     * Get script name from URL (for use as parameter in self::make, self::check, etc.)
     *
     * @param string $url
     * @return string
     */
    public static function getScriptNameFromUrl ( $url )
    {
        $path = parse_url($url, PHP_URL_PATH);
        $len  = strlen($path);
        if ( $len == 0  ||  '/' == $path{$len-1} ) {
            return "";
        }
        return basename($path);
    }

    /**
     * Get name of currently executed script (need to check signature of incoming message using self::check)
     *
     * @return string
     */
    public static function getOurScriptName ()
    {

        return self::getScriptNameFromUrl( \Yii::$app->request->url);
    }
    /**
     * Creates a signature
     *
     * @param array $arrParams  associative array of parameters for the signature
     * @param string $strSecretKey
     * @return string
     */
    public static function make ( $strScriptName, $arrParams, $strSecretKey )
    {
        $arrFlatParams = self::makeFlatParamsArray($arrParams);
        return md5( self::makeSigStr($strScriptName, $arrFlatParams, $strSecretKey) );
    }
    /**
     * Verifies the signature
     *
     * @param string $signature
     * @param array $arrParams  associative array of parameters for the signature
     * @param string $strSecretKey
     * @return bool
     */
    public static function check ( $signature, $strScriptName, $arrParams, $strSecretKey )
    {
        return (string)$signature === self::make($strScriptName, $arrParams, $strSecretKey);
    }
    /**
     * Returns a string, a hash of which coincide with the result of the make() method.
     * WARNING: This method can be used only for debugging purposes!
     *
     * @param array $arrParams  associative array of parameters for the signature
     * @param string $strSecretKey
     * @return string
     */
    static function debug_only_SigStr ( $strScriptName, $arrParams, $strSecretKey ) {
        return self::makeSigStr($strScriptName, $arrParams, $strSecretKey);
    }
    private static function makeSigStr ( $strScriptName, array $arrParams, $strSecretKey ) {
        unset($arrParams['pg_sig']);

        ksort($arrParams);
        array_unshift($arrParams, $strScriptName);
        array_push   ($arrParams, $strSecretKey);
        return join(';', $arrParams);
    }

    private static function makeFlatParamsArray ( $arrParams, $parent_name = '' )
    {
        $arrFlatParams = array();
        $i = 0;
        foreach ( $arrParams as $key => $val ) {

            $i++;
            if ( 'pg_sig' == $key )
                continue;

            /**
             * Имя делаем вида tag001subtag001
             * Чтобы можно было потом нормально отсортировать и вложенные узлы не запутались при сортировке
             */
            $name = $parent_name . $key . sprintf('%03d', $i);
            if (is_array($val) ) {
                $arrFlatParams = array_merge($arrFlatParams, self::makeFlatParamsArray($val, $name));
                continue;
            }
            $arrFlatParams += array($name => (string)$val);
        }
        return $arrFlatParams;
    }
    /********************** singing XML ***********************/
    /**
     * make the signature for XML
     *
     * @param string|SimpleXMLElement $xml
     * @param string $strSecretKey
     * @return string
     */
    public static function makeXML ( $strScriptName, $xml, $strSecretKey )
    {
        $arrFlatParams = self::makeFlatParamsXML($xml);
        return self::make($strScriptName, $arrFlatParams, $strSecretKey);
    }
    /**
     * Verifies the signature of XML
     *
     * @param string|SimpleXMLElement $xml
     * @param string $strSecretKey
     * @return bool
     */
    public static function checkXML ( $strScriptName, $xml, $strSecretKey )
    {
        if ( ! $xml instanceof SimpleXMLElement ) {
            $xml = new SimpleXMLElement($xml);
        }
        $arrFlatParams = self::makeFlatParamsXML($xml);
        return self::check((string)$xml->pg_sig, $strScriptName, $arrFlatParams, $strSecretKey);
    }
    /**
     * Returns a string, a hash of which coincide with the result of the makeXML() method.
     * WARNING: This method can be used only for debugging purposes!
     *
     * @param string|SimpleXMLElement $xml
     * @param string $strSecretKey
     * @return string
     */
    public static function debug_only_SigStrXML ( $strScriptName, $xml, $strSecretKey )
    {
        $arrFlatParams = self::makeFlatParamsXML($xml);
        return self::makeSigStr($strScriptName, $arrFlatParams, $strSecretKey);
    }
    /**
     * Returns flat array of XML params
     *
     * @param (string|SimpleXMLElement) $xml
     * @return array
     */
    private static function makeFlatParamsXML ( $xml, $parent_name = '' )
    {
        if ( ! $xml instanceof SimpleXMLElement ) {
            $xml = new SimpleXMLElement($xml);
        }
        $arrParams = array();
        $i = 0;
        foreach ( $xml->children() as $tag ) {

            $i++;
            if ( 'pg_sig' == $tag->getName() )
                continue;

            /**
             * Имя делаем вида tag001subtag001
             * Чтобы можно было потом нормально отсортировать и вложенные узлы не запутались при сортировке
             */
            $name = $parent_name . $tag->getName().sprintf('%03d', $i);
            if ( $tag->children()->count() > 0 ) {
                $arrParams = array_merge($arrParams, self::makeFlatParamsXML($tag, $name));
                continue;
            }
            $arrParams += array($name => (string)$tag);
        }
        return $arrParams;
    }
} 