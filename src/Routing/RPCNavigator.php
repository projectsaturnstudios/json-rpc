<?php

namespace JSONRPC\Routing;

use Illuminate\Database\Eloquent\Relations\Relation;
use JSONRPC\Enums\RPCErrorCode;
use JSONRPC\Exceptions\RPCResponseException;
use JSONRPC\Rpc\Controllers\RpcController;
use JSONRPC\RPCErrorObject;
use JSONRPC\RPCRequest;
use JSONRPC\RPCResponse;

class RPCNavigator
{
    protected array $methods = [];

    public function addMethod(RpcController $controller): static
    {
        $this->methods[$controller::_getMethodRoute()] = $controller;
        return $this;
    }

    public function dispatch(RPCRequest $request): RPCResponse
    {
        $method = $request->method;
        return $this->$method($request);
    }

    public function getMethods(): array
    {
        return $this->methods;
    }

    public static function boot(): void
    {
        app()->singleton('jsonrpc.router', static fn () => new static());
    }

    /**
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws RPCResponseException
     */
    public function __call(string $method, array $args): mixed
    {
        if($args[0] instanceof RPCRequest) {
            // @todo - implement middleware
            $methodsplosion = explode('/', $method);
            $morph = $methodsplosion[0];
            if(array_key_exists($morph, $this->methods))
            {
                $action = new $this->methods[$morph]();
                if(count($methodsplosion) > 1)
                {
                    $method = $methodsplosion[1];
                    return $action->$method($args[0]);
                }
                else
                {
                    return $action->handle($args[0]);
                }
            }

            return (new RPCResponse(id:$args[0]->id, error: new RPCErrorObject(
                RPCErrorCode::INVALID_REQUEST, 'Invalid method call or arguments provided.'
            )));
        }
        else
        {
            throw new \BadMethodCallException("Method $method does not exist or is not callable with the provided arguments.");
        }
    }
}
