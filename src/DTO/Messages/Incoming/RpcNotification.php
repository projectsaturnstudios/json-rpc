<?php

namespace Superconductor\Rpc\DTO\Messages\Incoming;

use Superconductor\Rpc\DTO\Messages\RpcMessage;

class RpcNotification extends RpcMessage
{
    public function __construct(
        string $method,
        ?array $params = null,
    ) {
        parent::__construct(method: $method, params: $params);
    }

    public static function fromRpcNotification(RpcNotification $notification): RpcNotification
    {
        return $notification;
    }

    public static function fromRpcRequest(RpcNotification $notification): RpcNotification
    {
        return self::fromRpcNotification($notification);
    }
}
