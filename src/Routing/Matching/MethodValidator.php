<?php

namespace ProjectSaturnStudios\RpcServer\Routing\Matching;

use ProjectSaturnStudios\RpcServer\RemoteProcedureCall;
use ProjectSaturnStudios\RpcServer\Interfaces\RpcValidatorContract;
use ProjectSaturnStudios\RpcServer\Interfaces\ProcedureCallRequestContract;

class MethodValidator implements RpcValidatorContract
{
    public function matches(RemoteProcedureCall $procedure_call, ProcedureCallRequestContract $request): bool
    {
        $path = rtrim($request->getPathInfo(), '/') ?: '/';

        return preg_match($procedure_call->getCompiled()->getRegex(), rawurldecode($path));
    }
}
