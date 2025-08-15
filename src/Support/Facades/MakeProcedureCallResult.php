<?php

namespace ProjectSaturnStudios\RpcServer\Support\Facades;

use Illuminate\Support\Facades\Facade;
use ProjectSaturnStudios\RpcServer\Enums\RpcErrorCode;
use ProjectSaturnStudios\RpcServer\DTO\Requesting\RpcMessageID;
use ProjectSaturnStudios\RpcServer\Interfaces\RpcResultBodyContract;
use ProjectSaturnStudios\RpcServer\Builders\ProcedureCallResultFactory;
use ProjectSaturnStudios\RpcServer\Interfaces\ProcedureCallErrorContract;
use ProjectSaturnStudios\RpcServer\Interfaces\ProcedureCallResultContract;

/**
 * @see ProcedureCallResultFactory
 * @method static ProcedureCallErrorContract error(RpcMessageID $id, RpcErrorCode $code, string $message, ?RpcResultBodyContract $data = null)
 * @method static ProcedureCallResultContract result(RpcMessageID $id, ?RpcResultBodyContract $results = null)
 */
class MakeProcedureCallResult extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return ProcedureCallResultFactory::class;
    }
}
