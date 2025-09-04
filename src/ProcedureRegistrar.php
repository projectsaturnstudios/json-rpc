<?php

namespace Superconductor\Rpc;

use Illuminate\Container\Container;

use Superconductor\Rpc\Enums\RPCErrorCode;
use Illuminate\Container\Attributes\Singleton;
use Superconductor\Rpc\DTO\Messages\Outgoing\RpcError;
use Superconductor\Rpc\DTO\Messages\Outgoing\RpcResult;
use Superconductor\Rpc\DTO\Messages\Incoming\RpcRequest;
use Superconductor\Rpc\DTO\Messages\Incoming\RpcNotification;

#[Singleton]
class ProcedureRegistrar
{
    protected array $procedures = [];

    public function __construct(
        protected Container $app,
    ) {}

    public function method(string $method, string $action): ProcedureRoute
    {
        $procedure = new ProcedureRoute($method, $action, $this);

        $this->procedures[$method] = $procedure;
        return $procedure;
    }

    public function getProcedures(): array
    {
        return $this->procedures;
    }

    public function call(RpcRequest $message): null|RpcResult|RpcError
    {
        $method = $message->method;
        if(!isset($this->procedures[$method])) return new RpcError($message->id, RPCErrorCode::INVALID_PARAMS, "Method '{$method}'  not found");
        return ($this->procedures[$method])->execute($message);
    }

    public function notify(RpcNotification $message): bool|RpcError
    {
        $method = $message->method;
        if(!isset($this->procedures[$method])) return new RpcError(null, RPCErrorCode::INVALID_PARAMS, "Method '{$method}' not found");
        return ($this->procedures[$method])->execute($message);
    }

    public static function boot(): void
    {
        app()->singleton('rpc', fn(Container $app) => new static($app));
    }
}
