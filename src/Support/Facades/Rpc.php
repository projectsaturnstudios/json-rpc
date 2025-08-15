<?php

namespace ProjectSaturnStudios\RpcServer\Support\Facades;

use Illuminate\Support\Facades\Facade;
use ProjectSaturnStudios\RpcServer\Routing\ProcedureCallRegistrar;
use ProjectSaturnStudios\RpcServer\RpcServer;
use ProjectSaturnStudios\RpcServer\RemoteProcedureCall;

/**
 * @method static RemoteProcedureCall procedure(string $uri, array|string|callable|null $action = null)
 * @method static ProcedureCallRegistrar prefix(string $prefix)
 * @method static ProcedureCallRegistrar middleware(string|array|null $middleware)
 * @method static \ProjectSaturnStudios\RpcServer\Interfaces\ProcedureCallResultContract dispatch(\ProjectSaturnStudios\RpcServer\Interfaces\ProcedureCallRequestContract $request)
 *
 *
 * @see RpcServer
 */
class RPC extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return RpcServer::class;
    }
}
