<?php

namespace ProjectSaturnStudios\RpcServer\Providers;

use Illuminate\Container\Container;
use ProjectSaturnStudios\RpcServer\Console\Commands\ListProceduresCommand;
use ProjectSaturnStudios\RpcServer\Console\Commands\MakeProcedureControllerCommand;
use ProjectSaturnStudios\RpcServer\DTO\IO\RpcErrorResult;
use ProjectSaturnStudios\RpcServer\DTO\IO\RpcRequest;
use ProjectSaturnStudios\RpcServer\DTO\IO\RpcResult;
use ProjectSaturnStudios\RpcServer\DTO\Requesting\RpcMessageID;
use ProjectSaturnStudios\RpcServer\Interfaces\ProcedureCallErrorContract;
use ProjectSaturnStudios\RpcServer\Interfaces\ProcedureCallRequestContract;
use ProjectSaturnStudios\RpcServer\Interfaces\ProcedureCallResultContract;
use ProjectSaturnStudios\RpcServer\Routing\CallableDispatcher;
use ProjectSaturnStudios\RpcServer\RpcRouter;
use ProjectSaturnStudios\RpcServer\RpcServer;
use ProjectSaturnStudios\LaravelDesignPatterns\Providers\BaseServiceProvider;

class RpcServerServiceProvider extends BaseServiceProvider
{
    protected array $config = [
        'rpc' => __DIR__ . '/../../config/rpc.php',
    ];

    protected array $publishable_config = [
        ['key' => 'rpc', 'file_path' => __DIR__ . '/../../config/rpc.php', 'groups' => ['rpc']],
    ];

    protected array $routes = [
        __DIR__ . '/../../routes/rpc.php',
    ];

    protected array $commands = [
        ListProceduresCommand::class,
        MakeProcedureControllerCommand::class,
    ];
    protected array $bootables = [
        RpcRouter::class,
        RpcServer::class,
        CallableDispatcher::class,

    ];

    protected function mainBooted(): void
    {
        // Bind the facade classes to the container
        app()->singleton(RpcServer::class);
        app()->singleton(\ProjectSaturnStudios\RpcServer\Builders\ProcedureCallResultFactory::class);
        
        app()->bind(ProcedureCallRequestContract::class, function(Container $app, array $args = []) {
            // Safe defaults; actual requests are instance-bound during dispatch
            $id = $args[0] ?? new RpcMessageID(null);
            $method = $args[1] ?? '';
            $params = $args[2] ?? null;

            return new RpcRequest(
                id: $id,
                method: $method,
                params: $params,
            );
        });

        app()->bind(ProcedureCallResultContract::class, fn(Container $app, array $args) => new RpcResult(
            id: $args[0],
            result: $args[1] ?? null,
            error: $args[2] ?? null,
        ));

        app()->bind(ProcedureCallErrorContract::class, fn(Container $app, array $args) => new RpcErrorResult(
            id: $args[0],
            error: $args[1],

        ));
    }
}
