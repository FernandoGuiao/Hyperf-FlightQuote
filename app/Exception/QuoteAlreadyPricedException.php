<?php

namespace App\Exception;

use Swoole\Http\Status;

class QuoteAlreadyPricedException extends BusinessException
{
    public function __construct()
    {
        parent::__construct(
            Status::UNPROCESSABLE_ENTITY,
            'Quote already priced, please make another search'
        );
    }

}