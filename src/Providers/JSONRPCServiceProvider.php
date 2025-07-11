<?php

namespace JSONRPC\Providers;

use Illuminate\Support\ServiceProvider;
use JSONRPC\Console\Commands\ListMethodsCommand;
use JSONRPC\RemoteProcedureCall;
use JSONRPC\Routing\RPCNavigator;

class JSONRPCServiceProvider extends ServiceProvider
{

    protected array $commands = [
        ListMethodsCommand::class,
    ];

    public function register(): void
    {

    }

    public function boot(): void
    {
        RemoteProcedureCall::boot();
        RPCNavigator::boot();
        $this->registerRPCMethods();
        $this->commands($this->commands);
    }

    protected function registerRPCMethods():  void
    {

        //$this->loadRoutesFrom(__DIR__.'/../../routes/rpc.php');

        // Check if there is a tools.php route in the main source's routes folder
        $mainToolsRoutePath = base_path('routes/rpc.php');
        if (file_exists($mainToolsRoutePath)) {
            $this->loadRoutesFrom($mainToolsRoutePath);
        }
    }
}
