<?php

namespace JSONRPC;

use Spatie\LaravelData\Data;

class RPCNotification extends RPCRequest
{
    public function __construct(
        string $method,
        ?array $params = null,
    )
    {
        parent::__construct($method, $params, null);
    }
}
