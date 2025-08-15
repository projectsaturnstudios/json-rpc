<?php

namespace ProjectSaturnStudios\RpcServer\Builders;

use ReflectionException;
use Illuminate\Container\Container;
use ProjectSaturnStudios\RpcServer\Enums\RpcErrorCode;
use ProjectSaturnStudios\RpcServer\DTO\Resulting\RpcError;
use ProjectSaturnStudios\RpcServer\DTO\Requesting\RpcMessageID;
use ProjectSaturnStudios\RpcServer\Interfaces\RpcResultBodyContract;
use ProjectSaturnStudios\RpcServer\Interfaces\ProcedureCallErrorContract;
use ProjectSaturnStudios\RpcServer\Interfaces\ProcedureCallResultContract;

class ProcedureCallResultFactory
{
    /**
     * @param Container $app
     */
    public function __construct(
        protected Container $app
    ) {}

    public function error(RpcMessageID $id, RpcErrorCode $code, string $message, ?RpcResultBodyContract $data = null): ProcedureCallErrorContract
    {
        return $this->app->make(ProcedureCallErrorContract::class, [
            $id, new RpcError($code, $message, $data),
        ]);
    }

    public function result(RpcMessageID $id, ?RpcResultBodyContract $results = null): ProcedureCallResultContract
    {
        return $this->app->make(ProcedureCallResultContract::class, [
            $id, $results, null,
        ]);
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public static function boot(): void
    {
        app()->bind(static::class, fn($app) => new static($app));
    }
}
