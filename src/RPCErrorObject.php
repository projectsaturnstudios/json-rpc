<?php

namespace JSONRPC;

use JSONRPC\Enums\RPCErrorCode;
use Spatie\LaravelData\Data;

class RPCErrorObject extends Data
{
    public function __construct(
        public readonly RPCErrorCode $code,
        public readonly string $message,
        public readonly mixed $data = null
    ) {}

    public function toArray(): array
    {
        $results = [
            'code' => $this->code,
            'message' => $this->message,
        ];

        if (!is_null($this->data)) {
            $results['data'] = $this->data;
        }

        return $results;
    }

}
