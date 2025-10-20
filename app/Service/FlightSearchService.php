<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\Search\FlightSearchForm;
use App\Enum\FlightProvidersEnum;
use Carbon\Carbon;
use Hyperf\Cache\Cache;
use Hyperf\Collection\Arr;
use Hyperf\Context\Context;
use Hyperf\Coroutine\Parallel;
use Hyperf\Stringable\Str;
use function Hyperf\Config\config;
use function Hyperf\Support\make;

class FlightSearchService
{
    const QUOTE_ID_KEY = 'QUOTE_ID';

    public function search(FlightSearchForm $form): array
    {
        $quoteId = Str::uuid()->toString();
        $parallel = new Parallel();

        $providers = FlightProvidersEnum::cases();
        foreach ($providers as $provider) {
            $parallel->add(function () use ($provider, $form, $quoteId) {
                Context::set(self::QUOTE_ID_KEY, $quoteId);
                return $provider->getInstance()->search($form);
            },
                $provider->value
            );
        }
        $results = $parallel->wait();
        $this->saveQuoteByProvider($quoteId, $results);
        $results = $this->sortResult($results);

        return [
            'quoteId' => $quoteId,
            'expiresAt' => Carbon::now()->addSeconds(config('quote.ttl'))->format('Y-m-d H:i:s'),
            'results' => $results,
        ];
    }


    private function saveQuoteByProvider(string $quoteId, array $results): void
    {
        $cache = make(Cache::class);
        $cache->set(
            self::QUOTE_ID_KEY. ':' . $quoteId,
            ['fareAlreadyPriced' => false, 'results' => $results],
            config('quote.ttl')
        );
    }

    private function sortResult(array $result)
    {
        $result = Arr::flatten($result);
        //TODO: Ordenar pelo o que seria mais significativo par ao negocio: preco, hora de partida, etc..
        return $result;
    }
}
