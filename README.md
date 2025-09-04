# Superconductor RPC

[![Latest Version on Packagist](https://img.shields.io/packagist/v/superconductor/rpc.svg?style=flat-square)](https://packagist.org/packages/superconductor/rpc)
[![Total Downloads](https://img.shields.io/packagist/dt/superconductor/rpc.svg?style=flat-square)](https://packagist.org/packages/superconductor/rpc)
[![License](https://img.shields.io/packagist/l/superconductor/rpc.svg?style=flat-square)](https://packagist.org/packages/superconductor/rpc)

A Laravel package for implementing the JSON-RPC 2.0 protocol with type safety and Laravel integration.

## Requirements

- Laravel 10.0, 11.0, or 12.0
- PHP 8.2 or greater
- Spatie Laravel Data ^4.11

## Installation

Install the package via composer:

```bash
composer require superconductor/rpc
```

The package will be auto-discovered by Laravel's package discovery. If you're using an older version of Laravel, add the service provider to your `config/app.php`:

```php
'providers' => [
    // ...
    Superconductor\Rpc\Providers\RpcServiceProvider::class,
],
```

## Usage

### Register Procedures

In your `routes/web.php` or any autoloaded routing file:

```php
use Superconductor\Rpc\Support\Facades\RPC;

RPC::method('math/add', MathController::class);
RPC::method('math/subtract', MathController::class.'@subtract');
```

### Create Procedure Classes

```php
<?php

namespace App\Rpc\Procedures;

use Superconductor\Rpc\Rpc\Procedures\RpcProcedure;
use Superconductor\Rpc\DTO\Messages\Outgoing\RpcResult;
use Superconductor\Rpc\DTO\Messages\Outgoing\RpcError;

class MathController extends RpcProcedure
{
    public function handle(array $params): RpcResult|RpcError
    {
        $result = $params['a'] + $params['b'];

        return new RpcResult(request()->get('id'), $result);
    }

    public function subtract(array $params): RpcResult|RpcError
    {
        $result = $params['a'] - $params['b'];

        return new RpcResult(request()->get('id'), $result);
    }
}
```

### Handle JSON-RPC Requests

```php
use Superconductor\Rpc\DTO\Messages\RpcMessage;
use Superconductor\Rpc\Support\Facades\RPC;

// Parse JSON-RPC request
$jsonrpc = '{"jsonrpc": "2.0", "method": "math/add", "params": {"a": 5, "b": 3}, "id": 1}';
$request = RpcMessage::fromJsonRpc($jsonrpc);

// Execute the procedure
$response = RPC::call($request);

// Send response back to client
return response()->json($response->toJsonRpc());
```

## Advanced Usage

### Custom Request DTOs

Create type-safe request objects using Spatie Laravel Data:

```php
<?php

use Superconductor\Rpc\DTO\Messages\Incoming\RpcRequest;
use Superconductor\Rpc\Support\Attributes\UsesRpcRequest;

#[UsesRpcRequest(CalculateRequest::class)]
class CalculatorController extends RpcProcedure
{
    public function calculate(CalculateRequest $request): RpcResult|RpcError
    {
        $result = match($request->operation) {
            'add' => $request->a + $request->b,
            'subtract' => $request->a - $request->b,
            'multiply' => $request->a * $request->b,
            'divide' => $request->b !== 0 ? $request->a / $request->b : throw new DivisionByZeroError(),
        };

        return new RpcResult($request->id, $result);
    }
}

class CalculateRequest extends RpcRequest
{
    public function __construct(
        int $id,
        public readonly float $a,
        public readonly float $b,
        public readonly string $operation,
    ) {
        parent::__construct($id, 'calculate', [
            'a' => $a,
            'b' => $b,
            'operation' => $operation,
        ]);
    }

    public static function fromRpcRequest(RpcRequest $request): static
    {
        return new self(
            $request->id,
            ...$request->params
        );
    }
}
```

### Notifications

Handle fire-and-forget operations:

```php
use Superconductor\Rpc\DTO\Messages\Incoming\RpcNotification;

// Parse notification (no ID, no response expected)
$jsonrpc = '{"jsonrpc": "2.0", "method": "user/logActivity", "params": {"action": "login"}}';
$notification = RpcMessage::fromJsonRpc($jsonrpc);

// Fire and forget - no response
RPC::notify($notification);
```

### Error Handling

```php
public function divide(array $params): RpcResult|RpcError
{
    if ($params['b'] === 0) {
        return $this->returnError(
            request()->get('id'),
            RPCErrorCode::INVALID_PARAMS,
            "Division by zero is not allowed"
        );
    }

    $result = $params['a'] / $params['b'];
    return new RpcResult(request()->get('id'), $result);
}
```

## Architecture

### Core Components

- **`ProcedureRegistrar`**: Manages RPC method registration and execution
- **`ProcedureRoute`**: Handles individual procedure routing and parameter binding
- **`RpcMessage`**: Base class for all JSON-RPC messages with serialization
- **`RpcProcedure`**: Abstract base class for all RPC procedures

### Message Types

- **`RpcRequest`**: Represents JSON-RPC requests with IDs (expect responses)
- **`RpcNotification`**: Represents JSON-RPC notifications (fire-and-forget)
- **`RpcResult`**: Represents successful responses
- **`RpcError`**: Represents error responses with proper error codes

### Error Codes

The package includes all standard JSON-RPC 2.0 error codes:

```php
enum RPCErrorCode: int
{
    case PARSE_ERROR = -32700;
    case INVALID_REQUEST = -32600;
    case METHOD_NOT_FOUND = -32601;
    case INVALID_PARAMS = -32602;
    case INTERNAL_ERROR = -32603;
    case SERVER_ERROR = -32000;
}
```

## Testing

```bash
composer test
```

## API Reference

### RPC Facade

#### `method(string $method, string $action): ProcedureRoute`
Register an RPC method with its handler class.

#### `call(RpcRequest $message): RpcResult|RpcError|null`
Execute an RPC call and return the response.

#### `notify(RpcNotification $message): bool|RpcError`
Send a notification (fire-and-forget operation).

### RpcMessage Class

#### `fromJsonRpc(string|array $message): RpcMessage`
Parse a JSON-RPC message from string or array format.

#### `toJsonRpc(bool $toString = false): array|string`
Serialize the message to JSON-RPC format.

## Credits

- [Angel Gonzalez](https://github.com/projectsaturnstudios)