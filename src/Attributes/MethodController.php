<?php

namespace JSONRPC\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]
class MethodController
{
    public function __construct(
        public readonly string $route
    ) {}
}
