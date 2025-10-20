<?php

namespace App\FlightProvider;

use App\Dto\Search\FlightSearchForm;

interface FlightProviderInterface
{
    public const PROVIDER_ID = 0;

    public function search(FlightSearchForm $form): array;

    public function savePrice(string $quoteId, mixed $flight, mixed $fare);

    public function saveRawQuote(mixed $response);

    public function book($priceId, $travelers);

}