<?php

namespace JSONRPC;

use Spatie\LaravelData\Data;

class RPCRequest extends Data
{
    public function __construct(
        public readonly string $method,
        public readonly ?array $params = null,
        public readonly string|int|null $id = null
    ) {}

    public function toArray(): array
    {
        $results = [
            'jsonrpc' => '2.0',
            'method' => $this->method,
        ];

        if (!is_null($this->params)) {
            $results['params'] = $this->params;
        }

        if (!is_null($this->id)) {
            $results['id'] = $this->id;
        }

        return $results;
    }

    /**
     * @return string
     * @throws \JsonException
     */
    public function toJsonRpc(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }
}
