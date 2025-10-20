<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Cases;

use Hyperf\Cache\Cache;
use Hyperf\Testing\TestCase;
use function Hyperf\Support\make;

/**
 * @internal
 * @coversNothing
 */
class ExampleTest extends TestCase
{
    public function setUp(): void
    {
        $cache = make(Cache::class);
        $cache->clear();
    }


    public function testExample()
    {
        $quoteResponse = $this->post('/flight/search',
            [
                "origin" => "SDU",
                "destination" => "CGH",
                "departureDate" => "2021-07-18",
                "returnDate" => "2021-07-20",
                "adults" => 1,
                "children" => 0,
                "infants" => 0,
                "tripType" => "OW"
            ]
        );
        $quoteResponse->assertOk();

        $data = [
            "quoteId" => $quoteResponse->json()['data']['quoteId'],
            "flightId" => $quoteResponse->json()['data']['results'][0]['flight']['id'],
            "fareId" => $quoteResponse->json()['data']['results'][0]['fares'][0]['id']
        ];


        $priceResponse = $this->post('/flight/price',
            $data
        );
        $priceResponse->assertOk();

        $bookResponse = $this->post('/flight/book', [
            'priceId' => $priceResponse->json()['data']['priceId'],
            'travelers' => [
                [
                    'name' => 'John Doe',
                    'document' => '123456789',
                    'email' => 'john.doe@example.com',
                ]
            ]
        ]);

        $bookResponse->assertOk();

    }

    public function tesExpired()
    {
        $quoteResponse = $this->post('/flight/search',
            [
                "origin" => "SDU",
                "destination" => "CGH",
                "departureDate" => "2021-07-18",
                "returnDate" => "2021-07-20",
                "adults" => 1,
                "children" => 0,
                "infants" => 0,
                "tripType" => "OW"
            ]
        );
        $quoteResponse->assertOk();


        $data = [
            "quoteId" => $quoteResponse->json()['data']['quoteId'],
            "flightId" => $quoteResponse->json()['data']['results'][0]['flight']['id'],
            "fareId" => $quoteResponse->json()['data']['results'][0]['fares'][0]['id']
        ];

        $priceResponse = $this->post('/flight/price',
            $data
        );

        $cache = make(Cache::class);
        $cache->clear();

        $bookResponse = $this->post('/flight/book', [
            'priceId' => $priceResponse->json()['data']['priceId'],
            'travelers' => [
                [
                    'name' => 'John Doe',
                    'document' => '123456789',
                    'email' => 'john.doe@example.com',
                ]
            ]
        ]);

        $bookResponse->assertUnprocessable();

    }
}
