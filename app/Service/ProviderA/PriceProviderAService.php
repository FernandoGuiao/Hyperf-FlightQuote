<?php

namespace App\Service\ProviderA;

use App\Dto\Search\FlightFarePrototype;
use App\Dto\Search\FlightOptionPrototype;
use App\Enum\FlightProvidersEnum;
use Hyperf\Cache\Cache;
use App\Exception\QuoteExpiredOrNotFoundException;
use Hyperf\Collection\Arr;
use function Hyperf\Support\make;
use function Hyperf\Config\config;


class PriceProviderAService
{
    public function __construct(
        private $normalizeSearchResponseService = new NormalizeSearchResponseProviderAService
    ) {}


    public function save(string $quoteId, mixed $flight, mixed $fare, string $priceId)
    {
        $rawQuote = $this->getRawQuote($quoteId);
        $rawOption = $this->getRawOption($rawQuote, $flight);
        $rawFare = $this->getRawFare($rawQuote, $fare, $rawOption);
        $this->reservePrice($rawOption, $rawFare, $priceId);
    }

    private function getRawQuote(string $quoteId): NormalizeSearchResponseProviderAService
    {
        $cache = make(Cache::class);
        $rawQuote = $cache->get('RAW_QUOTE_PROVIDER_A' . ':' . $quoteId);
        if (empty($rawQuote)) {
            throw new QuoteExpiredOrNotFoundException();
        }

        return $rawQuote;
    }

    private function getRawOption(NormalizeSearchResponseProviderAService $rawQuote, FlightOptionPrototype $flightOption): array
    {

        return Arr::first($rawQuote->rawResponse['VoosIda']['Viagens'], function ($rawOption) use ($flightOption, $rawQuote) {
            $rawOptionPrototype = $rawQuote->setFlightPrototype($rawOption);
            return $rawOptionPrototype->id === $flightOption->flight->id;
        });
    }

    private function getRawFare(NormalizeSearchResponseProviderAService $rawQuote, FlightFarePrototype $fareOption, array $rawOption): array
    {
        return Arr::first($rawQuote->outboundFaresById[$rawOption['ViagensId']]['fares'], function ($rawOption) use ($fareOption, $rawQuote) {
            $rawFarePrototype = $rawQuote->setFarePrototype($rawOption);
            return $rawFarePrototype->id === $fareOption->id;
        });
    }

    private function reservePrice(array $rawOption, array $rawFare, string $priceId)
    {
        $cache = make(Cache::class);
        $cache->set(
            $priceId,
            [
                'provider' => FlightProvidersEnum::ProviderA,
                'rawOption' => $rawOption,
                'rawFare' => $rawFare,
            ],
            config('price.ttl')
        );

    }
}