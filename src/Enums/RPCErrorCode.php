<?php

namespace Superconductor\Rpc\Enums;

enum RPCErrorCode: int
{
    case PARSE_ERROR = -32700;
    case INVALID_REQUEST = -32600;
    case METHOD_NOT_FOUND = -32601;
    case INVALID_PARAMS = -32602;
    case INTERNAL_ERROR = -32603;
    case SERVER_ERROR = -32000;

    public function isClientError(): bool
    {
        return $this->value < 0;
    }

    public function isServerError(): bool
    {
        return $this->value >= -32099 && $this->value <= -32000;
    }
}
