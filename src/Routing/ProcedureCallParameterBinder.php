<?php

namespace ProjectSaturnStudios\RpcServer\Routing;

use Illuminate\Support\Arr;
use ProjectSaturnStudios\RpcServer\Interfaces\ProcedureCallRequestContract;
use ProjectSaturnStudios\RpcServer\RemoteProcedureCall;

class ProcedureCallParameterBinder
{
    public function __construct(
        protected RemoteProcedureCall $procedure_call
    ) {}

    public function parameters($request): array
    {
        $parameters = $this->bindPathParameters($request);

        return $this->replaceDefaults($parameters);
    }

    protected function bindPathParameters(ProcedureCallRequestContract $request): array
    {
        $path = '/'.ltrim($request->getPathInfo(), '/');

        preg_match($this->procedure_call->compiled->getRegex(), $path, $matches);

        return $this->matchToKeys(array_slice($matches, 1));
    }

    protected function matchToKeys(array $matches): array
    {
        if (empty($parameterNames = $this->procedure_call->parameterNames())) {
            return [];
        }

        $parameters = array_intersect_key($matches, array_flip($parameterNames));

        return array_filter($parameters, function ($value) {
            return is_string($value) && strlen($value) > 0;
        });
    }

    protected function replaceDefaults(array $parameters): array
    {
        foreach ($parameters as $key => $value) {
            $parameters[$key] = $value ?? Arr::get($this->procedure_call->defaults, $key);
        }

        foreach ($this->procedure_call->defaults as $key => $value) {
            if (! isset($parameters[$key])) {
                $parameters[$key] = $value;
            }
        }

        return $parameters;
    }
}
