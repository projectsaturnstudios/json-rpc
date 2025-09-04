<?php

namespace Superconductor\Rpc\DTO\Messages\Outgoing;

use Superconductor\Rpc\DTO\Messages\RpcMessage;

class RpcResult extends RpcMessage
{
    public function __construct(int $id, array $result) {
        parent::__construct(id: $id, result: $result);
    }
}
