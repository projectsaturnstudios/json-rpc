<?php

namespace ProjectSaturnStudios\RpcServer\DTO\IO;

use ProjectSaturnStudios\RpcServer\Enums\RpcRequestType;
use ProjectSaturnStudios\RpcServer\DTO\Resulting\RpcError;
use ProjectSaturnStudios\RpcServer\DTO\Requesting\RpcMessageID;
use ProjectSaturnStudios\RpcServer\DTO\Resulting\RpcResultParams;
use ProjectSaturnStudios\RpcServer\DTO\Requesting\RpcMessageParams;
use ProjectSaturnStudios\RpcServer\Interfaces\ProcedureCallErrorContract;
use ProjectSaturnStudios\RpcServer\Interfaces\ProcedureCallResultContract;

class RpcErrorResult extends RpcResult implements ProcedureCallErrorContract
{
    public function __construct(
        RpcMessageID $id,
        RpcError $error,
    ) {
        parent::__construct($id, null, $error);
    }


}
