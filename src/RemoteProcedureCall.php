<?php

namespace JSONRPC;

use JSONRPC\Routing\RPCNavigator;
use JSONRPC\Rpc\Controllers\RpcController;
use JSONRPC\Support\Facades\RPCRouter;

class RemoteProcedureCall
{
    public function method(string $method, string $method_class) : RPCNavigator
    {
        /** @var RpcController $rpc_controller */
        $rpc_controller = new $method_class();
        return RPCRouter::addMethod($rpc_controller);
    }

    public static function boot(): void
    {
        app()->singleton('rpc', function ($app) {
            $results = new static();

            return $results;
        });
    }
}
