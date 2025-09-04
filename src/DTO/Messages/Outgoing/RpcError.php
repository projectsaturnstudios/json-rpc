<?php

namespace Superconductor\Rpc\DTO\Messages\Outgoing;

use Superconductor\Rpc\DTO\Messages\RpcMessage;
use Superconductor\Rpc\Enums\RPCErrorCode;

class RpcError extends RpcMessage
{
    public function __construct(
        ?int $id,
        RpcErrorCode|int $code,
        string $message,
        ?array $data = null,
    ) {
        parent::__construct(id: $id, error: [
            'code' => $code?->value ?? $code,
            'message' => $message,
            'data' => $data,
        ]);
    }
}
