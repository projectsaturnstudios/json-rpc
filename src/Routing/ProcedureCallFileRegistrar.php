<?php

namespace ProjectSaturnStudios\RpcServer\Routing;

use ProjectSaturnStudios\RpcServer\RpcRouter;

class ProcedureCallFileRegistrar
{
    protected RpcRouter $router;

    public function __construct(RpcRouter $router)
    {
        $this->router = $router;
    }

    public function register(string $procedure_calls): void
    {
        $router = $this->router;

        require $procedure_calls;
    }
}
