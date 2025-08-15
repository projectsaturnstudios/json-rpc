<?php

namespace ProjectSaturnStudios\RpcServer\DTO\Resulting;

use stdClass;
use ProjectSaturnStudios\RpcServer\Interfaces\ArrayableContract;
use ProjectSaturnStudios\RpcServer\Interfaces\RpcResultBodyContract;

readonly class RpcResultParams implements RpcResultBodyContract
{
    public function __construct(
        public array|ArrayableContract|null $params = null
    ) {}

    public function toValue(): array|ArrayableContract|null
    {
        return $this->params;
    }

    public function toArray(): array
    {
        if ($this->params instanceof ArrayableContract) {
            return $this->params->toArray();
        }

        if (is_array($this->params)) {


            return $this->params;
        }

        return [];
    }
}
