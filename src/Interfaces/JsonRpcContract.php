<?php

namespace ProjectSaturnStudios\RpcServer\Interfaces;

interface JsonRpcContract extends ArrayableContract
{
    public function toJsonRpc(): array;
}
