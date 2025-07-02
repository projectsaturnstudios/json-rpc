<?php

namespace JSONRPC\Rpc\Controllers;

use JSONRPC\Attributes\MethodController;

abstract class RpcController
{
    public static function _getMethodControllerAttribute(): ?MethodController
    {
        $attribute = (new \ReflectionClass(new static))->getAttributes(\JSONRPC\Attributes\MethodController::class);
        return $attribute[0]->newInstance();
    }

    public function getMethodControllerAttribute(): ?MethodController
    {
        return self::_getMethodControllerAttribute();
    }

    public function getMethodRoute(): string
    {
        $attribute = $this->getMethodControllerAttribute();
        if ($attribute === null) {
            throw new \RuntimeException('MethodController attribute not found.');
        }
        return $attribute->route;
    }

    public static function _getMethodRoute(): string
    {
        $attribute = self::_getMethodControllerAttribute();
        if ($attribute === null) {
            throw new \RuntimeException('MethodController attribute not found.');
        }
        return $attribute->route;
    }
}
