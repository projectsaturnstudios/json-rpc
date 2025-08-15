<?php

namespace ProjectSaturnStudios\RpcServer\Exceptions;

use ProjectSaturnStudios\RpcServer\Enums\RpcErrorCode;

class NotFoundRpcException extends RpcException
{
    public function __construct(string $message = '', ?\Throwable $previous = null, int $code = 0)
    {
        parent::__construct(RpcErrorCode::METHOD_NOT_FOUND, $message, $previous, $code);
    }
}
