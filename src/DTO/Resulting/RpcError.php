<?php

namespace ProjectSaturnStudios\RpcServer\DTO\Resulting;

use ProjectSaturnStudios\RpcServer\Enums\RpcErrorCode;
use ProjectSaturnStudios\RpcServer\Interfaces\RpcResultBodyContract;

readonly class RpcError implements RpcResultBodyContract
{
    public function __construct(
        public RpcErrorCode $code,
        public string $message,
        public ?RpcResultBodyContract $data = null
    ) {}

    public function toArray(): array
    {
        $results = [
            'code' => $this->code->value,
            'message' => $this->message,
        ];

        if (isset($this->data)) $results['data'] = $this->data->toValue();

        return $results;
    }

    public function toValue(): array
    {
        return $this->toArray();
    }
}
