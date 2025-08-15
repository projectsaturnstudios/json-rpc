<?php

use ProjectSaturnStudios\RpcServer\Support\Facades\RPC;
use ProjectSaturnStudios\RpcServer\Interfaces\ProcedureCallRequestContract;

if(has_sample_procedures())
{
    RPC::procedure('hello_world', function (ProcedureCallRequestContract $request) {
        return procedure_call_result(
            $request->id(), ['message' => 'Hello, World!']
        );
    });
}

RPC::procedure('ping', function (ProcedureCallRequestContract $request) {
    return procedure_call_result(
        $request->id(), []
    );
});

