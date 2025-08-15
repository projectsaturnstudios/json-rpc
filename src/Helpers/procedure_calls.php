<?php

use ProjectSaturnStudios\RpcServer\Enums\RpcErrorCode;
use ProjectSaturnStudios\RpcServer\DTO\Requesting\RpcMessageID;
use ProjectSaturnStudios\RpcServer\Interfaces\ArrayableContract;
use ProjectSaturnStudios\RpcServer\DTO\Resulting\RpcResultParams;
use ProjectSaturnStudios\RpcServer\Interfaces\ProcedureCallResultContract;
use ProjectSaturnStudios\RpcServer\Support\Facades\MakeProcedureCallResult;

if(!function_exists('procedure_call_result')) {

    function procedure_call_result(RpcMessageID $id, array|ArrayableContract|null $result = null): ProcedureCallResultContract
    {
        $is_success = (is_array($result) && (!isset($result['error'])))
            || ($result instanceof ArrayableContract && (!isset($result->toArray()['error'])));

        return $is_success
            ? MakeProcedureCallResult::result($id, new RpcResultParams($result))
            : MakeProcedureCallResult::error($id, RpcErrorCode::INTERNAL_ERROR, 'An error occurred while processing the procedure call.', new RpcResultParams($result));
    }
}
