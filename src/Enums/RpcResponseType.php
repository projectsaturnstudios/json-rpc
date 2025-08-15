<?php

namespace ProjectSaturnStudios\RpcServer\Enums;

enum RpcResponseType: string
{
    case RESULT = 'result';
    case ERROR = 'error';
}
