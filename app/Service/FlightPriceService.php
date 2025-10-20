<?php

namespace App\Service;

use App\Enum\FlightProvidersEnum;
use App\Exception\QuoteAlreadyPricedException;
use App\Exception\QuoteExpiredOrNotFoundException;
use Carbon\Carbon;
use Hyperf\Cache\Cache;
use Hyperf\Collection\Arr;
use function Hyperf\Support\make;
use function Hyperf\Config\config;


class FlightPriceService
{

    public function price($quoteId, $flightId, $fareId)
    {
        $quote = $this->getQuote($quoteId);

        [$providerId, $flight, $fare] = $this->getQuoteObjects($quote, $flightId, $fareId);
        $priceId = $this->saveProviderPrice($quoteId, $providerId, $flight, $fare);

        return [
            'priceId' => $priceId,
            'expires_at' => Carbon::now()
                ->addSeconds(config('price.ttl'))
                ->subSeconds(config('ttl.safety_margin'))
                ->format('Y-m-d H:i:s'),
        ];
    }

    private function getQuote($quoteId)
    {
        $cache = make(Cache::class);
        $quote = $cache->get(FlightSearchService::QUOTE_ID_KEY . ':' . $quoteId);
        if (empty($quote)) {
            throw new QuoteExpiredOrNotFoundException();
        }
        $fareAlreadyPriced = Arr::get($quote,'fareAlreadyPriced');
        if ($fareAlreadyPriced) {
            throw new QuoteAlreadyPricedException();
        }

        return $quote['results'];
    }

    private function getQuoteObjects($quote, $flightId, $fareId)
    {
        $providerId = explode('-', $fareId)[0];
        $flight = $this->findFlight($quote[$providerId], $flightId);
        $fare = $this->findFare($flight, $fareId);
        return[$providerId, $flight, $fare];
    }

    private function findFlight(array $quote, string $flightId)
    {
        $flight = Arr::first($quote, function ($flightOption) use ($flightId) {
            return $flightOption->flight->id === $flightId;
        });
        if (empty($flight)) {
            throw new QuoteExpiredOrNotFoundException();
        }

        return $flight;
    }

    private function findFare(mixed $flight, string $fareId)
    {
        $fare = Arr::first($flight->fares, function ($fareOption) use ($fareId) {
            return $fareId === $fareOption->id;
        });
        if (empty($fare)) {
            throw new QuoteExpiredOrNotFoundException();
        }

        return $fare;
    }

    private function saveProviderPrice(string $quoteId, mixed $providerId, mixed $flight, mixed $fare): string
    {
        $providerInstance = FlightProvidersEnum::from($providerId)->getInstance();
        $priceId = $providerInstance->savePrice($quoteId, $flight, $fare);
        return $priceId;
    }
}