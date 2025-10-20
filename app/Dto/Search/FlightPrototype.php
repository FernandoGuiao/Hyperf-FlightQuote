<?php

namespace App\Dto\Search;

use Carbon\Carbon;

class FlightPrototype
{

    public string $id;
    public string $providerId;
    public string $origin;
    public string $destination;
    public Carbon $departureDate;
    public Carbon $arrivalDate;
    public string $cia;
    public int $duration;

    /* @var array{FlightLegPrototype} $legs */
    public array $legs;


    public function getHashId(): string
    {
        return md5(json_encode($this));
    }

    public function setHashId()
    {
        $this->id = $this->getHashId();
    }
}
