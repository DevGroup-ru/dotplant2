<?php

namespace app\components\swap\provider;

use app\modules\shop\models\CurrencyRateProvider;
use Ivory\HttpAdapter\HttpAdapterInterface;
use Swap\Exception\Exception;
use Swap\Model\CurrencyPair;
use Swap\Model\Rate;
use Swap\Provider\AbstractProvider;
use Swap\Swap;

/**
 * Class MultiProvider
 * @package app\components\swap\provider
 */
class CurrencyRateMultiProvider extends AbstractProvider
{
    /**
     * @var int Main provider id
     */
    public $mainProvider;

    /**
     * @var int[] Second provider ids
     */
    public $secondProvider;

    /**
     * @var int Critical rate difference in percent
     */
    public $criticalDifference;

    public function __construct(
        HttpAdapterInterface $httpAdapter,
        $secondProvider,
        $mainProvider,
        $criticalDifference = 20
    ) {
        parent::__construct($httpAdapter);
        $this->mainProvider = $mainProvider;
        $this->secondProvider = $secondProvider;
    }

    public function fetchRate(CurrencyPair $currencyPair)
    {
        $providerIds = [
            (int) $this->mainProvider,
            (int) $this->secondProvider
        ];
        /** @var CurrencyRateProvider[] $providers */
        $providers = CurrencyRateProvider::find()
            ->where(['id' => $providerIds])
            ->orderBy(['FIELD (`id`, ' . implode(',', $providerIds) . ')' => ''])
            ->all();
        if (count($providers) !== 2) {
            throw new Exception('One of providers not found');
        }
        $rates = [];
        foreach ($providers as $provider) {
            try {
                $providerHandler = $provider->getImplementationInstance($this->httpAdapter);
                if ($providerHandler !== null) {
                    $swap = new Swap($providerHandler);
                    $rate = $swap
                        ->quote($currencyPair->getBaseCurrency() . '/' . $currencyPair->getQuoteCurrency())
                        ->getValue();
                    $rates[] = floatval($rate);
                } else {
                    throw new Exception('Provider "' . $provider->name . '" not found');
                }
            } catch (\Exception $e) {
                throw new Exception('One or more currency providers did not return result');
            }
        }
        $min = min($rates);
        $max = max($rates);
        return new Rate(
            $max - $min >= $max * $this->criticalDifference / 100 ? $max : $rates[0],
            new \DateTime()
        );
    }
}
