<?php

declare(strict_types=1);

use App\Controller\FlightController;
use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET', 'POST', 'HEAD'], '/', 'App\Controller\IndexController@index');

Router::addGroup('/flight', function () {
    Router::post('/search', [FlightController::class, 'search']);
    Router::post('/price', [FlightController::class, 'price']);
    Router::post('/book', [FlightController::class, 'book']);
});
