<?php

namespace ProjectSaturnStudios\RpcServer\DTO\IO;

use Closure;
use ProjectSaturnStudios\RpcServer\Enums\RpcRequestType;
use ProjectSaturnStudios\RpcServer\DTO\Requesting\RpcMessageID;
use ProjectSaturnStudios\RpcServer\DTO\Requesting\RpcMessageParams;
use ProjectSaturnStudios\RpcServer\Interfaces\ProcedureCallRequestContract;

class RpcRequest extends JsonRpcMessage implements ProcedureCallRequestContract
{
    public readonly RpcRequestType $state;
    protected Closure $routeResolver;

    public function __construct(
        public readonly RpcMessageID $id,
        public readonly string $method,
        public readonly ?RpcMessageParams $params = null,
    ) {
        parent::__construct();

        $this->state = (!is_null($id->toValue()))
            ? RpcRequestType::REQUEST
            : RpcRequestType::NOTIFICATION;
    }

    public function id(): RpcMessageID
    {
        return $this->id;
    }

    public function method(): string
    {
        return $this->method;
    }
    public function params(): ?RpcMessageParams
    {
        return $this->params;
    }

    public function getPathInfo(): string
    {
        return $this->method;
    }

    public function toJsonRpc(): array
    {
        $results = [
            'jsonrpc' => $this->jsonrpc,
            'method' => $this->method,
        ];

        if (isset($this->params)) $results['params'] = $this->params?->toArray();
        if (isset($this->id)) $results['id'] = $this->id->toValue();

        return $results;
    }

    public function setRouteResolver(callable $callback): static
    {
        $this->routeResolver = $callback;

        return $this;
    }
}
