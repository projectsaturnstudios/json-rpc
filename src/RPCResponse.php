<?php

namespace JSONRPC;

use JSONRPC\Exceptions\RPCResponseException;
use Spatie\LaravelData\Data;

class RPCResponse extends Data
{
    /**
     * @param string|int $id
     * @param string|array|null $result
     * @param RPCErrorObject|null $error
     * @throws RPCResponseException
     */
    public function __construct(
        public readonly string|int $id,
        public readonly string|array|null $result = null,
        public readonly ?RPCErrorObject $error = null
    ) {
        if(is_null($result) && is_null($error)) throw RPCResponseException::missingResultOrError();
        if((!is_null($result)) && (!is_null($error))) throw RPCResponseException::onlyResultOrError();
    }

    public function toArray(): array
    {
        $results = [
            'jsonrpc' => '2.0',
            'id' => $this->id,
        ];

        if (!is_null($this->result)) {
            $results['result'] = $this->result;
        }
        else
        {
            $results['error'] = $this->error?->toArray();
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
