<?php

namespace ProjectSaturnStudios\RpcServer\Exceptions;

use ProjectSaturnStudios\RpcServer\Enums\RpcErrorCode;

class JsonRpcResponseException extends RpcException
{
    public function __construct(string $message = '', ?\Throwable $previous = null, int $code = 0)
    {
        parent::__construct(RpcErrorCode::SERVER_ERROR, $message, $previous, $code);
    }
}
