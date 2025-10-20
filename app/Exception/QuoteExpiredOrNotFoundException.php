<?php

namespace App\Exception;

use Swoole\Http\Status;

class QuoteExpiredOrNotFoundException extends BusinessException
{
    public function __construct()
    {
        parent::__construct(
            Status::UNPROCESSABLE_ENTITY,
            'Quote expired or not found'
        );
    }

}