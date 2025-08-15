<?php

namespace ProjectSaturnStudios\RpcServer\Interfaces;

use Illuminate\Routing\Route;
use ProjectSaturnStudios\RpcServer\RemoteProcedureCall;
use ProjectSaturnStudios\RpcServer\Rpc\Procedures\ProcedureController;

interface ProcedureDispatcherContract
{
    /**
     * Dispatch a request to a given controller and method.
     *
     * @param  RemoteProcedureCall  $procedure_call
     * @param  mixed  $procedure
     * @param  string  $method
     * @return mixed
     */
    public function dispatch(RemoteProcedureCall $procedure_call, mixed $procedure, string $method): mixed;

    /**
     * Get the middleware for the controller instance.
     *
     * @param  ProcedureController  $procedure
     * @param  string  $method
     * @return array
     */
    public function getMiddleware($procedure, string $method): array;
}
