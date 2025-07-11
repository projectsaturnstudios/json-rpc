<?php

namespace JSONRPC\Support\Facades;

use Illuminate\Support\Facades\Facade;

class Rpc extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'rpc';
    }
}
