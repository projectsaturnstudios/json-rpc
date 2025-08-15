<?php

namespace ProjectSaturnStudios\RpcServer\Interfaces;

use ProjectSaturnStudios\RpcServer\RemoteProcedureCall;

interface CallableDispatcherContract
{
    /**
     * Dispatch a request to a given callable.
     *
     * @param  RemoteProcedureCall $procedure_call
     * @param  callable  $callable
     * @return mixed
     */
    public function dispatch(RemoteProcedureCall $procedure_call, callable $callable): mixed;
}
