<?php

namespace ProjectSaturnStudios\RpcServer\Rpc\Procedures;

use ProjectSaturnStudios\RpcServer\DTO\Requesting\RpcMessageParams;
use ProjectSaturnStudios\RpcServer\Interfaces\ProcedureCallResultContract;
use ProjectSaturnStudios\RpcServer\Interfaces\ProcedureControllerContract;
use ProjectSaturnStudios\RpcServer\Interfaces\ProcedureCallRequestContract;

abstract class ProcedureController implements ProcedureControllerContract
{
    public function __invoke(ProcedureCallRequestContract $request): ProcedureCallResultContract
    {
        return procedure_call_result(
            $request->id(),
            $this->handle($request->params())
        );
    }

    public function handle(?RpcMessageParams $params = null): array
    {
        $results =  [
            'error' => 'Not implemented',
        ];

        if($params) $results['params'] = $params->toValue();

        return $results;
    }
}
