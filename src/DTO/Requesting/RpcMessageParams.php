<?php

namespace ProjectSaturnStudios\RpcServer\DTO\Requesting;

use ProjectSaturnStudios\RpcServer\Interfaces\ArrayableContract;
use ProjectSaturnStudios\RpcServer\Interfaces\RpcRequestBodyContract;

class RpcMessageParams implements RpcRequestBodyContract
{
    public function __construct(
        public readonly ?ArrayableContract $params = null
    ) {}

    public function toValue(): ?array
    {
        return $this->params?->toArray();
    }

    public function toArray(): array
    {
        return $this->params ? (array) $this->params : [];
    }
}
