<?php

namespace ProjectSaturnStudios\RpcServer\Interfaces;

use ProjectSaturnStudios\RpcServer\RemoteProcedureCall;

interface RpcValidatorContract
{
    /**
     * Validate a given rule against an RPC and request.
     *
     * @param  RemoteProcedureCall  $procedure_call
     * @param  ProcedureCallRequestContract  $request
     * @return bool
     */
    public function matches(RemoteProcedureCall $procedure_call, ProcedureCallRequestContract $request): bool;
}
