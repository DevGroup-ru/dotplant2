<?php

namespace app\components\swap\provider;

use app\modules\shop\models\CurrencyRateProvider;
use Ivory\HttpAdapter\HttpAdapterInterface;
use Swap\Exception\Exception;
use Swap\Model\CurrencyPair;
use Swap\Model\Rate;
use Swap\Provider\AbstractProvider;
use Swap\Swap;

class MarginProvider extends AbstractProvider
{
    /**
     * @var int Provider id
     */
    protected $provider;

    /**
     * @var float fixed margin value
     */
    protected $fixedMargin;

    /**
     * @var float margin multiplier value
     */
    protected $marginMultiplier;

    public function __construct(
        HttpAdapterInterface $httpAdapter,
        $provider,
        $fixedMargin = null,
        $marginMultiplier = null
    ) {
        parent::__construct($httpAdapter);
        $this->provider = $provider;
        $this->fixedMargin = $fixedMargin;
        $this->marginMultiplier = $marginMultiplier;
    }

    public function fetchRate(CurrencyPair $currencyPair)
    {
        /** @var CurrencyRateProvider $provider */
        $provider = CurrencyRateProvider::findOne($this->provider);
        if ($provider === null) {
            throw new Exception('Provider not found');
        }
        try {
            $providerHandler = $provider->getImplementationInstance($this->httpAdapter);
            $swap = new Swap($providerHandler);
            $rate = $swap
                ->quote($currencyPair->getBaseCurrency() . '/' . $currencyPair->getQuoteCurrency())
                ->getValue();
            if ($this->marginMultiplier !== null && $this->marginMultiplier > 1) {
                $rate *= $this->marginMultiplier;
            }
            if ($this->fixedMargin !== null && $this->fixedMargin > 0) {
                $rate += $this->fixedMargin;
            }
        } catch (\Exception $e) {
            throw new Exception('Calculating error');
        }
        return new Rate($rate, new \DateTime());
    }
}
