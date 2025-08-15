<?php

namespace ProjectSaturnStudios\RpcServer\Enums;

enum RpcRequestType: string
{
    case REQUEST = 'request';
    case NOTIFICATION = 'notification';
}
