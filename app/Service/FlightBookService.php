<?php

namespace App\Service;

use App\Enum\FlightProvidersEnum;
use App\Exception\QuoteAlreadyPricedException;
use App\Exception\QuoteExpiredOrNotFoundException;
use Hyperf\Cache\Cache;
use Hyperf\Collection\Arr;
use function Hyperf\Support\make;

class FlightBookService
{
    public function book($priceId, $travelers)
    {
        $price = $this->getPrice($priceId);
        $provider = $price['provider'];
        $providerInstance = $provider->getInstance();
        $bookingId = $providerInstance->book($priceId, $travelers);

        return ['bookingId' => $bookingId];
    }

    private function getPrice($priceId)
    {
        $cache = make(Cache::class);
        $price = $cache->get($priceId);
        if (empty($price)) {
            throw new QuoteExpiredOrNotFoundException();
        }

        return $price;
    }
}