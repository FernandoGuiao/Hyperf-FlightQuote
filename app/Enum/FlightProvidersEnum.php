<?php

namespace App\Enum;

use App\FlightProvider\FlightProviderA;
use App\FlightProvider\FlightProviderInterface;

enum FlightProvidersEnum: int
{
    case ProviderA = 1;
//    case ProviderB = 2;

    public function getInstance(): FlightProviderInterface
    {
        return match ($this) {
            self::ProviderA => new FlightProviderA,
//            self::ProviderB => new FlightProviderB,
        };
    }



}