<?php

namespace App\FlightProvider;

use App\Dto\Search\FlightSearchForm;

class FlightProviderB implements FlightProviderInterface
{
    const PROVIDER_ID = 2;
    public function search(FlightSearchForm $form): array
    {
        //passar o formulário para o provider.
        $json = file_get_contents(BASE_PATH . '/storage/Mocks/ProviderB.json');
        return json_decode($json, true);
    }

    public function savePrice(string $quoteId, mixed $flight, mixed $fare)
    {
        // TODO: Implement savePrice() method.
    }

    public function saveRawQuote(mixed $response)
    {
        // TODO: Implement saveRawQuote() method.
    }

    public function book($priceId, $travelers)
    {
        // TODO: Implement book() method.
    }
}