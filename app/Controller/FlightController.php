<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\Search\FlightSearchForm;
use App\Request\FlightBookFormRequest;
use App\Request\FlightPriceFormRequest;
use App\Request\FlightSearchFormRequest;
use App\Resource\FlightPriceResource;
use App\Resource\FlightSearchResource;
use App\Service\FlightBookService;
use App\Service\FlightSearchService;
use App\Service\FlightPriceService;
use Hyperf\HttpServer\Contract\ResponseInterface;

class FlightController extends AbstractController
{
    public function __construct(
        private FlightSearchService $flightSearchService,
        private FlightPriceService $flightPriceService,
        private FlightBookService $flightBookService
    ) {}

    public function search(FlightSearchFormRequest $request)
    {
       $response = $this->flightSearchService->search(FlightSearchForm::fromRequest($request->validated()));
       return FlightSearchResource::make($response);
    }

    public function price(FlightPriceFormRequest $request)
    {
        $form = $request->validated();
        $response = $this->flightPriceService->price(
            $form['quoteId'],
            $form['flightId'],
            $form['fareId'],
        );
        return FlightPriceResource::make($response);
    }

    public function book(FlightBookFormRequest $request)
    {
        $form = $request->validated();
        $response = $this->flightBookService->book(
            $form['priceId'],
            $form['travelers']
        );
        return FlightPriceResource::make($response);
    }

}
