<?php

namespace Superconductor\Rpc\DTO\Messages\Incoming;

use Superconductor\Rpc\DTO\Messages\RpcMessage;

class RpcRequest extends RpcMessage
{
    public function __construct(
        int $id,
        string $method,
        ?array $params = null,
    ) {
        parent::__construct(id: $id, method: $method, params: $params);
    }

    public static function fromRpcRequest(RpcRequest $request): RpcRequest
    {
        return $request;
    }
}
