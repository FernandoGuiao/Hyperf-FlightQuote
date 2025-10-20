<?php

namespace App\FlightProvider;


use App\Dto\Search\FlightSearchForm;
use App\Service\FlightSearchService;
use App\Service\ProviderA\NormalizeSearchResponseProviderAService;
use App\Service\ProviderA\PriceProviderAService;
use Hyperf\Cache\Cache;
use Hyperf\Stringable\Str;
use Hyperf\Context\Context;
use function Hyperf\Support\make;
use function Hyperf\Config\config;

class FlightProviderA implements FlightProviderInterface
{
    public function __construct(
        private $normalizeSearchResponseService = new NormalizeSearchResponseProviderAService,
        private $priceService = new PriceProviderAService
    ) {}

    const PROVIDER_ID = 1;
    public function search(FlightSearchForm $form): array
    {
        $response = $this->sendSearchRequest($form);
        $rawResponse = $this->normalizeSearchResponseService->normalizeSearchResponse($response);
        $this->saveRawQuote($rawResponse);
        return $rawResponse->outboundFlights;
    }

    public function sendSearchRequest(FlightSearchForm $form): array
    {
        //mocked provider response
        $json = file_get_contents(BASE_PATH . '/storage/Mocks/ProviderA.json');
        return json_decode($json, true);
    }

    public function savePrice(string $quoteId, mixed $flight, mixed $fare): string
    {
       $priceId = Str::uuid()->toString();
       $this->priceService->save($quoteId, $flight, $fare, $priceId);

       return $priceId;
    }

    public function saveRawQuote(mixed $response): void
    {
        $cache = make(Cache::class);
        $cache->set(
            'RAW_QUOTE_PROVIDER_A' . ':' . Context::get(FlightSearchService::QUOTE_ID_KEY),
            $response,
            config('quote.ttl')
        );
    }

    public function book($priceId, $travelers)
    {
        // TODO: nesse ponto salvaria em um banco de dados SQL O book, viajantes, e oferta escolhida. retornaria o id da reserva
        return Str::uuid()->toString();

    }
}