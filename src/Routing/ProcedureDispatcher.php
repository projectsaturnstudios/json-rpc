<?php

namespace ProjectSaturnStudios\RpcServer\Routing;

use Illuminate\Support\Collection;
use Illuminate\Container\Container;
use Illuminate\Routing\ResolvesRouteDependencies;
use Illuminate\Routing\FiltersControllerMiddleware;
use ProjectSaturnStudios\RpcServer\RemoteProcedureCall;
use ProjectSaturnStudios\RpcServer\Interfaces\ProcedureDispatcherContract;

class ProcedureDispatcher implements ProcedureDispatcherContract
{
    use ResolvesRouteDependencies;
    use FiltersControllerMiddleware;
    /**
     * Create a new controller dispatcher instance.
     *
     * @param  Container  $container
     */
    public function __construct(
        protected Container $container
    ) {}

    public function dispatch(RemoteProcedureCall $procedure_call, mixed $procedure, string $method): mixed
    {
        $parameters = $this->resolveParameters($procedure_call, $procedure, $method);

        if (method_exists($procedure, 'callAction')) {
            return $procedure->callAction($method, $parameters);
        }

        return $procedure->{$method}(...array_values($parameters));
    }

    protected function resolveParameters(RemoteProcedureCall $procedure_call, mixed $procedure, string $method): array
    {
        return $this->resolveClassMethodDependencies(
            $procedure_call->parametersWithoutNulls(), $procedure, $method
        );
    }

    public function getMiddleware(mixed $procedure, string $method): array
    {
        if (! method_exists($procedure, 'getMiddleware')) {
            return [];
        }

        return (new Collection($procedure->getMiddleware()))
            ->reject(fn ($data) => static::methodExcludedByOptions($method, $data['options']))
            ->pluck('middleware')
            ->all();
    }
}
