<?php

namespace JSONRPC\Support\Facades;

use Illuminate\Support\Facades\Facade;
use JSONRPC\Routing\RPCNavigator;

/**
 * @method static RPCNavigator method(string $method, string $method_class)
 * @see \JSONRPC\RemoteProcedureCall
 */
class Rpc extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'rpc';
    }
}
