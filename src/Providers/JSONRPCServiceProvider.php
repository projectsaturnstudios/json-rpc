<?php

namespace JSONRPC\Providers;

use Illuminate\Support\ServiceProvider;
use JSONRPC\Routing\RPCNavigator;

class JSONRPCServiceProvider extends ServiceProvider
{

    public function register(): void
    {

    }

    public function boot(): void
    {
        RPCNavigator::boot();
    }
}
