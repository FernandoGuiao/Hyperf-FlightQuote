<?php

namespace App\Service\ProviderA;

use App\Dto\Search\FlightFarePrototype;
use App\Dto\Search\FlightIncludedLuggagePrototype;
use App\Dto\Search\FlightLegPrototype;
use App\Dto\Search\FlightOptionPrototype;
use App\Dto\Search\FlightPrototype;
use App\FlightProvider\FlightProviderA;
use Carbon\Carbon;
use Hyperf\Collection\Arr;

class NormalizeSearchResponseProviderAService
{
    public array $outboundFlights = [];
    public array $outboundLegsById = [];
public array $outboundFaresById = [];

    public array $rawResponse = [];


    /* @returns array{FlightOptionPrototype}*/
    public function normalizeSearchResponse(array $response): self
    {
        $this->rawResponse = $response;

        foreach ($response['TrechosViagem'] ?? [] as $flightleg) {
            $this->outboundLegsById[$flightleg['ViagensId']][] = $this->setFlightLegPrototype($flightleg);
            $this->outboundFaresById[$flightleg['ViagensId']]['fares'] = $flightleg['listaDeClasses'];
        }

        foreach ($response['VoosIda']['Viagens'] ?? [] as $flight) {
            $outboundFlight = $this->setFlightOptionPrototype($flight);
            $this->outboundFlights[$outboundFlight->flight->id] = $outboundFlight;
        }

        return $this;
    }

    public function setFlightOptionPrototype(array $flight): FlightOptionPrototype
    {
        $flightOption = new FlightOptionPrototype;
        $flightOption->flight = $this->setFlightPrototype($flight);
        $flightOption->fares = $this->setFlightFaresPrototype($flight);
        return $flightOption;
    }

    public function setFlightPrototype(array $flight): FlightPrototype
    {
        $flightPrototype = new FlightPrototype;
        $flightPrototype->cia = $flight['empresaAerea'];
        $flightPrototype->origin = Arr::first($this->outboundLegsById[$flight['ViagensId']])->origin;
        $flightPrototype->destination = Arr::last($this->outboundLegsById[$flight['ViagensId']])->destination;
        $flightPrototype->departureDate = Arr::first($this->outboundLegsById[$flight['ViagensId']])->departureDate;
        $flightPrototype->arrivalDate = Arr::last($this->outboundLegsById[$flight['ViagensId']])->arrivalDate;
        $flightPrototype->duration = (int) $flight['duracaoTotalFiltro'];
        $flightPrototype->providerId = $flight['ViagensId'];
        $flightPrototype->legs = $this->outboundLegsById[$flight['ViagensId']];
        $flightPrototype->setHashId();

        return $flightPrototype;
    }

    public function setFlightLegPrototype(mixed $flightleg): FlightLegPrototype
    {
        $flightLeg = new FlightLegPrototype;
        $flightLeg->cia = $flightleg['companhiaAerea'];
        $flightLeg->origin = $flightleg['aeroportoOrigem'];
        $flightLeg->destination = $flightleg['aeroportoDestino'];
        $flightLeg->departureDate = Carbon::createFromFormat('d/m/Y H:i', $flightleg['dataPartida']);
        $flightLeg->arrivalDate = Carbon::createFromFormat('d/m/Y H:i', $flightleg['dataChegada']);
        $flightLeg->flightNumber = $flightleg['voo'];
        $flightLeg->duration = $flightLeg->departureDate->diffInMinutes($flightLeg->arrivalDate);
        return $flightLeg;
    }

    public function setFlightFaresPrototype(array $flight): array
    {
        $fares = [];
        foreach (Arr::first($this->outboundFaresById[$flight['ViagensId']], default: [])  as $fare) {
            $farePrototype = $this->setFarePrototype($fare);
            $fares[] = $farePrototype;
        }
        return $fares;
    }

    public function setFarePrototype(mixed $fare): FlightFarePrototype
    {
        $farePrototype = new FlightFarePrototype;
        $farePrototype->id = FlightProviderA::PROVIDER_ID . '-' . md5(json_encode($fare));
        $farePrototype->total = intval($fare['Preco']['Total'] * 100);
        $farePrototype->class = $fare['Familia'];
        $farePrototype->currency = $fare['Preco']['Moeda'];
        $farePrototype->includedLuggage = [];
        if ($fare['BagagemInclusa']) {
            $farePrototype->includedLuggage = FlightIncludedLuggagePrototype::fromArray([
                ['quantity' => $fare['BagagemQuantidade'], 'weight' => $fare['BagagemPeso']]
            ]);
        }
        return $farePrototype;
    }


}