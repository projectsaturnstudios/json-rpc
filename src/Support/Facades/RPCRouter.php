<?php

namespace JSONRPC\Support\Facades;

use Illuminate\Support\Facades\Facade;
use JSONRPC\Rpc\Controllers\RpcController;
use JSONRPC\RPCRequest;

/**
 * @method static addMethod(RpcController $controller)
 * @method static dispatch(RPCRequest $request)
 * @method static getMethods()
 */
class RPCRouter extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'jsonrpc.router';
    }


}
