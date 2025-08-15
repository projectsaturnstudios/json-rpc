<?php

namespace ProjectSaturnStudios\RpcServer\Interfaces;

interface RpcExceptionContract
{
    public function getStatusCode(): int;
}
