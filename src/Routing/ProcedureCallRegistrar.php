<?php

namespace ProjectSaturnStudios\RpcServer\Routing;

use BackedEnum;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use ProjectSaturnStudios\RpcServer\RpcRouter;
use Illuminate\Routing\CreatesRegularExpressionRouteConstraints;

class ProcedureCallRegistrar
{
    use CreatesRegularExpressionRouteConstraints;

    protected RpcRouter $router;

    protected array $attributes = [];

    protected $passthru = ['any'];

    protected array $aliases = [
        'name' => 'as',
        'scopeBindings' => 'scope_bindings',
        'withoutScopedBindings' => 'scope_bindings',
        'withoutMiddleware' => 'excluded_middleware',
    ];

    protected array $allowedAttributes = [
        'as',
        'can',
        'procedure',
        'middleware',
        'missing',
        'name',
        'namespace',
        'prefix',
        'scopeBindings',
        'where',
        'withoutMiddleware',
        'withoutScopedBindings',
    ];

    /**
     * Create a new route registrar instance.
     *
     * @param  RpcRouter  $router
     */
    public function __construct(RpcRouter $router)
    {
        $this->router = $router;
    }

    public function attribute($key, $value): static
    {
        if (! in_array($key, $this->allowedAttributes)) {
            throw new InvalidArgumentException("Attribute [{$key}] does not exist.");
        }

        if ($key === 'middleware') {
            foreach ($value as $index => $middleware) {
                $value[$index] = (string) $middleware;
            }
        }

        $attributeKey = Arr::get($this->aliases, $key, $key);

        if ($key === 'withoutMiddleware') {
            $value = array_merge(
                (array) ($this->attributes[$attributeKey] ?? []), Arr::wrap($value)
            );
        }

        if ($key === 'withoutScopedBindings') {
            $value = false;
        }

        if ($value instanceof BackedEnum && ! is_string($value = $value->value)) {
            throw new InvalidArgumentException("Attribute [{$key}] expects a string backed enum.");
        }

        $this->attributes[$attributeKey] = $value;

        return $this;
    }

    /**
     * Create a procedure_call group with shared attributes.
     *
     * @param  \Closure|array|string  $callback
     * @return $this
     */
    public function group(callable|array|string $callback): static
    {
        $this->router->group($this->attributes, $callback);

        return $this;
    }
}
