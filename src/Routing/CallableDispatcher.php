<?php

namespace ProjectSaturnStudios\RpcServer\Routing;

use ReflectionFunction;
use Illuminate\Container\Container;
use Illuminate\Routing\ResolvesRouteDependencies;
use ProjectSaturnStudios\RpcServer\RemoteProcedureCall;
use ProjectSaturnStudios\RpcServer\Interfaces\CallableDispatcherContract;

class CallableDispatcher implements CallableDispatcherContract
{
    use ResolvesRouteDependencies;

    /**
     * The container instance.
     *
     * @var \Illuminate\Container\Container
     */
    protected $container;

    /**
     * Create a new callable dispatcher instance.
     *
     * @param  \Illuminate\Container\Container  $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Dispatch a request to a given callable.
     *
     * @param  \Illuminate\Routing\Route  $route
     * @param  callable  $callable
     * @return mixed
     */
    public function dispatch(RemoteProcedureCall $procedure_call, callable $callable): mixed
    {
        return $callable(...array_values($this->resolveParameters($procedure_call, $callable)));
    }

    /**
     * Resolve the parameters for the callable.
     *
     * @param  \Illuminate\Routing\Route  $route
     * @param  callable  $callable
     * @return array
     */
    protected function resolveParameters(RemoteProcedureCall $procedure_call, $callable)
    {
        return $this->resolveMethodDependencies($procedure_call->parametersWithoutNulls(), new ReflectionFunction($callable));
    }

    public static function boot(): void
    {
        app()->bind(CallableDispatcherContract::class, static::class);
    }
}
