<?php

namespace Superconductor\Rpc\Rpc\Procedures;

use ReflectionClass;
use Superconductor\Rpc\Enums\RPCErrorCode;
use Superconductor\Rpc\DTO\Messages\Outgoing\RpcError;
use Superconductor\Rpc\Support\Attributes\UsesRpcRequest;

abstract class RpcProcedure
{
    protected function returnError(?int $id, RPCErrorCode $code = RPCErrorCode::INTERNAL_ERROR, string $message = "Not Implemented", ?array $data = null): RpcError
    {
        return new RpcError($id, $code, $message, $data);
    }
    public static function hasUsesRpcRequest(): bool
    {
        $attribute = (new ReflectionClass(new static))->getAttributes(UsesRpcRequest::class);
        return isset($attribute[0]) && $attribute[0]->newInstance() instanceof UsesRpcRequest;
    }

    public static function getRpcMessageClass(): string
    {
        $attribute = (new ReflectionClass(new static))->getAttributes(UsesRpcRequest::class);
        /** @var UsesRpcRequest $attribute */
        $attribute = $attribute[0]->newInstance();
        return $attribute->action;
    }
}
