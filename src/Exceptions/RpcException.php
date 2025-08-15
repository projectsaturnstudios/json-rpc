<?php

namespace ProjectSaturnStudios\RpcServer\Exceptions;

use RuntimeException;
use ProjectSaturnStudios\RpcServer\Enums\RpcErrorCode;
use ProjectSaturnStudios\RpcServer\Interfaces\RpcExceptionContract;

class RpcException extends RuntimeException implements RpcExceptionContract
{
    public function __construct(
        private RpcErrorCode $statusCode,
        string $message = '',
        ?\Throwable $previous = null,
        int $code = 0,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode->value;
    }
}
