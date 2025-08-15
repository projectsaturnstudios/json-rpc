<?php

namespace ProjectSaturnStudios\RpcServer\Interfaces;

interface RpcResultBodyContract extends ArrayableContract
{
    public function toValue(): mixed;
}
