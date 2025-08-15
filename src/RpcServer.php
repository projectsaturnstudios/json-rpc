<?php

namespace ProjectSaturnStudios\RpcServer;

use Illuminate\Container\Attributes\Singleton;
use Illuminate\Container\Container;
use ProjectSaturnStudios\RpcServer\Interfaces\ProcedureCallRequestContract;
use ProjectSaturnStudios\RpcServer\Interfaces\ProcedureCallResultContract;
use ProjectSaturnStudios\RpcServer\Routing\ProcedureCallRegistrar;

#[Singleton]
class RpcServer
{
    protected ?ProcedureCallRequestContract $currentRequest = null;

    public function __construct(
        protected RpcRouter $router,
    ) {}

    public function procedure(string $method, array|string|callable|null $action = null): RemoteProcedureCall
    {
        return $this->router->registerProcedureCall($method, $action);
    }

    public function prefix(string $prefix): ProcedureCallRegistrar
    {
        return (new ProcedureCallRegistrar($this->router))->attribute('prefix', $prefix);
    }

    public function middleware(array|string|null $middleware): ProcedureCallRegistrar
    {
        return (new ProcedureCallRegistrar($this->router))->attribute('middleware', $middleware);
    }

    public function dispatch(ProcedureCallRequestContract $request): ProcedureCallResultContract
    {
        return $this->router->dispatch($request);
    }

    public static function boot(): void
    {
        app()->singleton(static::class, fn (Container $app) => new static(
            $app->make(RpcRouter::class)
        ));
    }

}
