<?php

namespace ProjectSaturnStudios\RpcServer\DTO\IO;

use stdClass;
use ProjectSaturnStudios\RpcServer\Enums\RpcResponseType;
use ProjectSaturnStudios\RpcServer\DTO\Resulting\RpcError;
use ProjectSaturnStudios\RpcServer\DTO\Requesting\RpcMessageID;
use ProjectSaturnStudios\RpcServer\DTO\Resulting\RpcResultParams;
use ProjectSaturnStudios\RpcServer\Interfaces\ProcedureCallResultContract;

class RpcResult extends JsonRpcMessage implements ProcedureCallResultContract
{
    public readonly RpcResponseType $state;
    public function __construct(
        public readonly RpcMessageID $id,
        public readonly ?RpcResultParams $result = null,
        public readonly ?RpcError $error = null,
    ) {
        parent::__construct();

        $this->state = (empty($this->error))
            ? RpcResponseType::RESULT
            : RpcResponseType::ERROR;
    }

    public function id(): RpcMessageID
    {
        return $this->id;
    }

    public function toJsonRpc(): array
    {
        $results =  [
            'jsonrpc' => $this->jsonrpc,
            'id' => $this->id->toValue(),
        ];

        if ($this->state === RpcResponseType::RESULT) {
            $results['result'] = $this->result?->toArray();
            if(is_array($results['result']) && empty($results['result'])) $results['result'] = new stdClass();
        } elseif ($this->state === RpcResponseType::ERROR) {
            $results['error'] = $this->error?->toArray();
        }

        return $results;
    }
}
