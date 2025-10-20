<?php

namespace App\Dto\Search;

class FlightFarePrototype
{

    public string $id;
    public int $total;
    public string $currency;
    public string $class;

    /* @var array{FlightIncludedLuggagePrototype} $includedLuggage */
    public array $includedLuggage;

}


