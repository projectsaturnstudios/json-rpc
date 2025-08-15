<?php

namespace ProjectSaturnStudios\RpcServer\DTO\IO;

use Spatie\LaravelData\Data;
use ProjectSaturnStudios\RpcServer\Interfaces\JsonRpcContract;

abstract class JsonRpcMessage extends Data implements JsonRpcContract
{
    public function __construct(
        public readonly float $jsonrpc = 2.0
    ) {}


}
