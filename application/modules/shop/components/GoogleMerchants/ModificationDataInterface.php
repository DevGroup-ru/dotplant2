<?php

namespace app\modules\shop\components\GoogleMerchants;


interface ModificationDataInterface
{
    public static function processData(ModificationDataEvent $event);
}