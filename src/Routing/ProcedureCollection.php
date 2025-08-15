<?php

namespace ProjectSaturnStudios\RpcServer\Routing;

use ProjectSaturnStudios\RpcServer\Exceptions\NotFoundRpcException;
use ProjectSaturnStudios\RpcServer\Interfaces\ProcedureCallRequestContract;
use ProjectSaturnStudios\RpcServer\RemoteProcedureCall;

class ProcedureCollection extends AbstractProcedureCollection
{
    protected array $procedures = [];
    protected array $method_list = [];
    protected array $action_list = [];
    protected array $all_procedures = [];

    public function getProcedureCalls(): array
    {
        return array_values($this->all_procedures);
    }

    /**
     * @param string|null $method
     * @return array<RemoteProcedureCall>
     */
    public function get(?string $method = null): array
    {
        return is_null($method)
            ? $this->getProcedureCalls()
            : (isset($this->procedures[$method]) ? [$this->procedures[$method]] : []);
    }

    public function add(RemoteProcedureCall $procedure_call): RemoteProcedureCall
    {
        $this->addToCollections($procedure_call);
        $this->addLookups($procedure_call);
        return $procedure_call;
    }

    public function match(ProcedureCallRequestContract $request): RemoteProcedureCall
    {
        $procedure_calls = $this->get($request->method());
        // @todo -pick it back up here with an abstract class for matchAgainstProcedureCalls

        $procedure_call  = $this->matchAgainstProcedureCalls($procedure_calls, $request);

        return $this->handleMatchedProcedureCall($request, $procedure_call);
    }

    protected function addToActionList($action, $route): void
    {
        $this->action_list[trim($action['procedure'], '\\')] = $route;
    }

    protected function addLookups(RemoteProcedureCall $procedure_call): void
    {
        if ($method = $procedure_call->getMethod()) $this->method_list[$method] = $procedure_call;
        $action = $procedure_call->getAction();
        if (isset($action['procedure'])) $this->addToActionList($action, $procedure_call);
    }

    protected function addToCollections(RemoteProcedureCall $procedure_call): void
    {
        $method = $procedure_call->getMethod();
        $this->procedures[$method] = $procedure_call;
        $this->all_procedures[$method] = $procedure_call;
    }

    protected function handleMatchedProcedureCall(?ProcedureCallRequestContract $request, RemoteProcedureCall $procedure_call): RemoteProcedureCall
    {
        if (!is_null($request)) return $procedure_call->bind($request);

        throw new NotFoundRpcException(sprintf(
            'The route %s could not be found.',
            $procedure_call->getMethod()
        ));
    }
}
