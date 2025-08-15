<?php

namespace ProjectSaturnStudios\RpcServer\Interfaces;

use Countable;
use IteratorAggregate;

interface ProcedureCollectionContract extends Countable, IteratorAggregate
{
    public function getProcedureCalls();
}
