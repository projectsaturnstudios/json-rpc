<?php

namespace Superconductor\Rpc\DTO\Messages;

use stdClass;
use Spatie\LaravelData\Data;
use Superconductor\Rpc\DTO\Messages\Outgoing\RpcError;
use Superconductor\Rpc\DTO\Messages\Incoming\RpcNotification;
use Superconductor\Rpc\DTO\Messages\Incoming\RpcRequest;
use Superconductor\Rpc\DTO\Messages\Outgoing\RpcResult;

abstract class RpcMessage extends Data
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?string $method = null,
        public readonly ?array $params = null,
        public readonly ?array $result = null,
        public readonly ?array $error = null,
    ) {}

    public static function fromJsonRpc(string|array $message): RpcMessage
    {
        if(is_string($message)) $message = json_decode($message, true);
        if(isset($message['error'])) return new RpcError($message['id'] ?? null, ...$message['error']);
        if(isset($message['result'])) return new RpcResult(id: $message['id'], result: $message['result']);
        if(!isset($message['id'])) return new RpcNotification(method: $message['method'], params: $message['params'] ?? null);
        return new RpcRequest(id: $message['id'], method: $message['method'], params: $message['params'] ?? null);
    }

    public function toJsonRpc(bool $toString = false): array|string
    {
        $results = [
            "jsonrpc" => "2.0"
        ];

        if(isset($this->id)) $results['id'] = $this->id;
        if(isset($this->method)) $results['method'] = $this->method;
        if(isset($this->params))
        {
            $results['params'] = array_map(function($item){
                return empty($item) ? new stdClass() : $item;
            }, $this->params);
        };

        if(isset($this->result))
        {
            $results['result'] = empty($this->result) ? new stdClass() : array_map(function($item){
                return empty($item) ? new stdClass() : $item;
            }, $this->result);
        }
        elseif(isset($this->error))
        {
            $results['error'] = array_map(function($item){
                return empty($item) ? new stdClass() : $item;
            }, $this->error);
        }


        return $toString ? json_encode($results) : $results;
    }
}
