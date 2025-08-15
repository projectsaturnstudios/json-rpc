<?php

namespace ProjectSaturnStudios\RpcServer\DTO\Requesting;

readonly class RpcMessageID
{
    public function __construct(
        public string|int|null $id = null
    ) {}

    public function toValue(): string|int|null
    {
        return $this->id;
    }
}
