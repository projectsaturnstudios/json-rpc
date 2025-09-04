<?php

namespace Superconductor\Rpc\Providers;

use ProjectSaturnStudios\LaravelDesignPatterns\Providers\BaseServiceProvider;
use Superconductor\Rpc\ProcedureRegistrar;

class RpcServiceProvider extends BaseServiceProvider
{
    protected array $config = [
        //'json-rpc' => __DIR__ . '/../../config/json-rpc.php',
    ];

    protected array $publishable_config = [
        //['key' => 'json-rpc', 'file_path' => __DIR__ . '/../../config/json-rpc.php', 'groups' => ['json-rpc']],
    ];

    protected array $routes = [
        //__DIR__ . '/../../routes/procedures.php',
    ];

    protected array $commands = [
        //ListProceduresCommand::class,
    ];
    protected array $bootables = [
        //RequestMessage::class,
    ];
    public function register(): void
    {
        $this->registerConfigs();
        ProcedureRegistrar::boot();
    }
}
