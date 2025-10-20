<?php

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class FlightPriceResource extends JsonResource
{
    public function toArray(): array
    {
        return parent::toArray();
    }
}
