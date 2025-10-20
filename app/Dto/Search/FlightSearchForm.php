<?php

declare(strict_types=1);

namespace App\Dto\Search;

use Carbon\Carbon;

class FlightSearchForm
{
    public string $origin;
    public string $destination;
    public Carbon $departureDate;
    public Carbon $returnDate;
    public int $adults;
    public int $children;
    public int $infants;
    public string $tripType;

    public static function fromRequest(array $searchParams): self
    {
        $instance = new self();
        $instance->origin = $searchParams['origin'];
        $instance->destination = $searchParams['destination'];
        $instance->departureDate = Carbon::createFromFormat('Y-m-d', $searchParams['departureDate']);
        $instance->returnDate = Carbon::createFromFormat('Y-m-d', $searchParams['returnDate']);
        $instance->adults = $searchParams['adults'];
        $instance->children = $searchParams['children'];
        $instance->infants = $searchParams['infants'];
        $instance->tripType = $searchParams['tripType'];
        return $instance;
    }



}
