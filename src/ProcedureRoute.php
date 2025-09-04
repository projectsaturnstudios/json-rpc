<?php

namespace Superconductor\Rpc;

use Superconductor\Rpc\DTO\Messages\Outgoing\RpcError;
use Superconductor\Rpc\DTO\Messages\Outgoing\RpcResult;
use Superconductor\Rpc\DTO\Messages\Incoming\RpcRequest;
use Superconductor\Rpc\DTO\Messages\Incoming\RpcNotification;
use Superconductor\Rpc\DTO\Messages\RpcMessage;
use Superconductor\Rpc\Rpc\Procedures\RpcProcedure;

class ProcedureRoute
{
    public readonly string $function;
    public readonly string $procedure_class;
    public readonly string $request_parameter;

    public function __construct(
        public readonly string       $method,
        public readonly string       $action,
        protected ProcedureRegistrar &$registrar
    )
    {
        $this->function = $this->getFunction($action);
        $this->procedure_class = $this->getProcedureClass($action);
        $this->request_parameter = $this->getRequestParameter($action);
    }

    private function getFunction(string $action): string
    {
        $results = 'handle';

        if (str_contains($action, '@')) {
            [$class_name, $action_name] = explode('@', $action);
            $results = $action_name;
        }

        return $results;
    }

    private function getProcedureClass(string $action): string
    {
        $results = $action;

        if (str_contains($action, '@')) {
            [$class_name, $action_name] = explode('@', $action);
            $results = $class_name;
        }

        return $results;
    }

    private function getRequestParameter(string $action): string
    {
        $results = RpcRequest::class;

        if (str_contains($action, '@')) [$action, $action_name] = explode('@', $action);

        /** @var class-string<RpcProcedure> $action */
        if($action::hasUsesRpcRequest() ?? false) $results = $action::getRpcMessageClass();

        return $results;
    }


    public function execute(RpcRequest|RpcNotification $message): null|RpcResult|RpcError|bool
    {
        $method = $this->function;

        /** @var RpcProcedure $procedure */
        $procedure = new $this->procedure_class();

        /** @var class-string<RpcRequest|RpcNotification> $req_param */
        $req_param = $this->request_parameter;

        $addl = $message->getAdditionalData();
        $request = $req_param::fromRpcRequest($message);
        $request->additional($addl);
        return $procedure->$method($request);
    }
}
