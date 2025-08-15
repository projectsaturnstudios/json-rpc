<?php

namespace ProjectSaturnStudios\RpcServer\Routing;

use Illuminate\Support\Collection;
use ProjectSaturnStudios\RpcServer\Interfaces\ProcedureCallRequestContract;
use ProjectSaturnStudios\RpcServer\RemoteProcedureCall;
use Traversable;
use ArrayIterator;
use ProjectSaturnStudios\RpcServer\Interfaces\ProcedureCollectionContract;

abstract class AbstractProcedureCollection implements ProcedureCollectionContract
{
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->getProcedureCalls());
    }

    public function count(): int
    {
        return count($this->getProcedureCalls());
    }

    protected function matchAgainstProcedureCalls(array $procedure_calls, ProcedureCallRequestContract $request, bool $includingMethod = true): ?RemoteProcedureCall
    {
        [$fallbacks, $procedure_calls] = (new Collection($procedure_calls))->partition(function (RemoteProcedureCall $procedure_call) {
            return $procedure_call->isFallback;
        });

        return $procedure_calls->merge($fallbacks)->first(
            fn (RemoteProcedureCall $procedure_call) => $procedure_call->matches($request, $includingMethod)
        );
    }

}
