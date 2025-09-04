<?php

namespace Superconductor\Rpc\Support\Facades;

use Illuminate\Support\Facades\Facade;
use Superconductor\Rpc\DTO\Messages\Outgoing\RpcError;
use Superconductor\Rpc\DTO\Messages\Incoming\RpcNotification;

/**
 * @method static \Superconductor\Rpc\ProcedureRoute method(string $method, string $action)
 * @method static bool|RpcError notify(RpcNotification $message)
 * @see \Superconductor\Rpc\ProcedureRegistrar
 */
class RPC extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'rpc';
    }
}
