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

namespace App\Exception\Handler;

use App\Exception\BusinessException;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class BusinessExceptionHandler extends ExceptionHandler
{
    public function __construct(protected StdoutLoggerInterface $logger)
    {
    }

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        {
            $data = json_encode([
                'code' => $throwable->getCode(),
                'message' => $throwable->getMessage(),
                'data' => method_exists($throwable, 'getData') ? $throwable->getData() : null,
            ], JSON_UNESCAPED_UNICODE);
            $this->stopPropagation();
            return $response
                ->withStatus($throwable->getCode())
                ->withBody(new SwooleStream($data))
                ->withHeader('Content-Type', 'application/json');
        }
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof BusinessException;
    }
}
