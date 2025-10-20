<?php

namespace App\Dto\Search;

use Carbon\Carbon;

class FlightLegPrototype
{

    public string $origin;
    public string $destination;
    public Carbon $departureDate;
    public Carbon $arrivalDate;
    public string $flightNumber;
    public string $cia;
    public int $duration;



}


