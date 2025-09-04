<?php

namespace Superconductor\Rpc\Support\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class UsesRpcRequest
{
    public function __construct(public readonly string $action) {}
}
