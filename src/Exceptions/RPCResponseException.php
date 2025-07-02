<?php

namespace JSONRPC\Exceptions;

class RPCResponseException extends \Exception
{
    public static function missingResultOrError() : static
    {
        return new static('RPC Response must contain either a result or an error.');
    }

    public static function onlyResultOrError() : static
    {
        return new static('RPC Response must contain either a result or an error, not both.');
    }
}
