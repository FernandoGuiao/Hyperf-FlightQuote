<?php

namespace App\Dto\Search;

class FlightIncludedLuggagePrototype
{
    public int $quantity;
    public int $weight;



    public static function fromArray(array $includedLuggages): array
    {
        $array = [];
        foreach ($includedLuggages as $includedLuggage) {
            $instance = new self();
            $instance->quantity = $includedLuggage['quantity'];
            $instance->weight = $includedLuggage['weight'];
            $array[] = $instance;
        }
        return $array;
    }

}


