<?php

namespace ProjectSaturnStudios\RpcServer\Interfaces;


use ProjectSaturnStudios\RpcServer\DTO\Requesting\RpcMessageID;

interface ProcedureCallRequestContract extends JsonRpcContract
{
    public function id(): RpcMessageID;
    public function method(): string;
    public function params(): string|RpcRequestBodyContract|null;

    public function getPathInfo(): string;
    public function setRouteResolver(callable $callback): static;
}
