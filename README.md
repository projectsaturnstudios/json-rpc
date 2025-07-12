# JSON-RPC Laravel Package

A Laravel package for implementing the JSON-RPC 2.0 protocol with elegant attribute-based routing and clean data handling.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/projectsaturnstudios/json-rpc.svg?style=flat-square)](https://packagist.org/packages/projectsaturnstudios/json-rpc)
[![Total Downloads](https://img.shields.io/packagist/dt/projectsaturnstudios/json-rpc.svg?style=flat-square)](https://packagist.org/packages/projectsaturnstudios/json-rpc)

## Features

- 🚀 **JSON-RPC 2.0 Compliant** - Full implementation of the JSON-RPC 2.0 specification
- 🎯 **Attribute-Based Routing** - Clean, declarative method routing using PHP 8 attributes
- 📦 **Laravel Data Integration** - Powered by Spatie Laravel Data for robust data handling
- 🛡️ **Comprehensive Error Handling** - Standard JSON-RPC error codes and custom exceptions
- 🎭 **Facade Support** - Laravel-style facade for easy access
- 🔧 **Extensible Architecture** - Easy to extend with custom controllers and middleware
- 📝 **Type Safety** - Full PHP 8.2+ type declarations throughout
- 🛣️ **Route File Integration** - Familiar Laravel routing patterns with `routes/rpc.php`

## Installation

Install the package via Composer:

```bash
composer require projectsaturnstudios/json-rpc
```

The package will automatically register itself via Laravel's auto-discovery.

## Quick Start

### 1. Create an RPC Controller

```php
<?php

namespace App\Http\Controllers\RPC;

use JSONRPC\Attributes\MethodController;
use JSONRPC\Rpc\Controllers\RpcController;
use JSONRPC\RPCRequest;
use JSONRPC\RPCResponse;

#[MethodController('math')]
class MathController extends RpcController
{
    public function handle(RPCRequest $request): RPCResponse
    {
        // Handle the main 'math' method
        return new RPCResponse(
            id: $request->id,
            result: ['message' => 'Math service is running', 'available_operations' => ['add', 'subtract', 'multiply', 'divide']]
        );
    }
    
    public function add(RPCRequest $request): RPCResponse
    {
        // Handle 'math/add' method
        $params = $request->params;
        
        if (!isset($params[0]) || !isset($params[1])) {
            return new RPCResponse(
                id: $request->id,
                error: new RPCErrorObject(
                    code: RPCErrorCode::INVALID_PARAMS,
                    message: 'Two numbers are required'
                )
            );
        }
        
        $result = $params[0] + $params[1];
        
        return new RPCResponse(
            id: $request->id,
            result: $result
        );
    }
    
    public function multiply(RPCRequest $request): RPCResponse
    {
        // Handle 'math/multiply' method
        $params = $request->params;
        
        if (!isset($params[0]) || !isset($params[1])) {
            return new RPCResponse(
                id: $request->id,
                error: new RPCErrorObject(
                    code: RPCErrorCode::INVALID_PARAMS,
                    message: 'Two numbers are required'
                )
            );
        }
        
        $result = $params[0] * $params[1];
        
        return new RPCResponse(
            id: $request->id,
            result: $result
        );
    }
}
```

### 2. Register Your Controllers

Create a `routes/rpc.php` file in your Laravel application:

```php
<?php

use JSONRPC\Support\Facades\Rpc;
use App\Http\Controllers\RPC\MathController;
use App\Http\Controllers\RPC\WeatherController;
use App\Http\Controllers\RPC\TodoController;

// Register RPC method controllers
Rpc::method('math', MathController::class);
Rpc::method('weather', WeatherController::class);
Rpc::method('todo', TodoController::class);

// You can also register namespaced methods
Rpc::method('api/calculator', MathController::class);
```

The package automatically loads this file and registers all your RPC methods.

### 3. Dispatch RPC Requests

```php
use JSONRPC\RPCRequest;
use JSONRPC\Support\Facades\RPCRouter;

$request = new RPCRequest(
    method: 'math/add',
    params: [5, 3],
    id: 1
);

$response = RPCRouter::dispatch($request);
echo $response->toJsonRpc();
// {"jsonrpc":"2.0","id":1,"result":8}
```

## Usage Examples

### Basic Request/Response

```php
use JSONRPC\RPCRequest;
use JSONRPC\RPCResponse;

// Create a request
$request = new RPCRequest(
    method: 'math/multiply',
    params: [4, 7],
    id: 1
);

// Create a successful response
$response = new RPCResponse(
    id: 1,
    result: 28
);

// Output JSON-RPC
echo $response->toJsonRpc();
// {"jsonrpc":"2.0","id":1,"result":28}
```

### Error Handling

```php
use JSONRPC\RPCResponse;
use JSONRPC\RPCErrorObject;
use JSONRPC\Enums\RPCErrorCode;

// Create an error response
$response = new RPCResponse(
    id: 1,
    error: new RPCErrorObject(
        code: RPCErrorCode::INVALID_PARAMS,
        message: 'Invalid parameters provided',
        data: ['expected' => 'array of numbers', 'received' => 'string']
    )
);
```

### Notifications (Fire and Forget)

```php
use JSONRPC\RPCNotification;

// Create a notification (no response expected)
$notification = new RPCNotification(
    method: 'logger/info',
    params: ['message' => 'User logged in', 'user_id' => 123]
);

echo $notification->toJsonRpc();
// {"jsonrpc":"2.0","method":"logger/info","params":{"message":"User logged in","user_id":123}}
```

### Advanced Controller Example

```php
<?php

namespace App\Http\Controllers\RPC;

use JSONRPC\Attributes\MethodController;
use JSONRPC\Rpc\Controllers\RpcController;
use JSONRPC\RPCRequest;
use JSONRPC\RPCResponse;
use JSONRPC\RPCErrorObject;
use JSONRPC\Enums\RPCErrorCode;

#[MethodController('weather')]
class WeatherController extends RpcController
{
    public function handle(RPCRequest $request): RPCResponse
    {
        // Default handler for 'weather'
        return new RPCResponse(
            id: $request->id,
            result: ['service' => 'weather', 'available_methods' => ['current', 'forecast', 'alerts']]
        );
    }
    
    public function current(RPCRequest $request): RPCResponse
    {
        try {
            $params = $request->params;
            
            // Validate required parameters
            if (!isset($params['location'])) {
                return new RPCResponse(
                    id: $request->id,
                    error: new RPCErrorObject(
                        code: RPCErrorCode::INVALID_PARAMS,
                        message: 'Missing required parameter: location'
                    )
                );
            }
            
            // Your weather service logic here
            $weatherData = $this->getCurrentWeather($params['location']);
            
            return new RPCResponse(
                id: $request->id,
                result: $weatherData
            );
            
        } catch (\Exception $e) {
            return new RPCResponse(
                id: $request->id,
                error: new RPCErrorObject(
                    code: RPCErrorCode::INTERNAL_ERROR,
                    message: 'Weather service unavailable'
                )
            );
        }
    }
    
    private function getCurrentWeather(string $location): array
    {
        // Your actual weather API logic
        return [
            'location' => $location,
            'temperature' => 72,
            'condition' => 'sunny',
            'humidity' => 45
        ];
    }
}
```

## Route Registration

### Using routes/rpc.php (Recommended)

Create a `routes/rpc.php` file in your Laravel application:

```php
<?php

use JSONRPC\Support\Facades\Rpc;
use App\Http\Controllers\RPC\MathController;
use App\Http\Controllers\RPC\WeatherController;
use App\Http\Controllers\RPC\TodoController;

// Basic method registration
Rpc::method('math', MathController::class);
Rpc::method('weather', WeatherController::class);
Rpc::method('todo', TodoController::class);

// Namespaced methods
Rpc::method('api/calculator', MathController::class);
Rpc::method('api/weather', WeatherController::class);

// Grouped registration
Rpc::method('services/math', MathController::class);
Rpc::method('services/weather', WeatherController::class);
```

### Alternative: Manual Registration

If you prefer to register methods manually in a service provider:

```php
use JSONRPC\Support\Facades\Rpc;
use JSONRPC\Support\Facades\RPCRouter;
use App\Http\Controllers\RPC\MathController;

// Using the Rpc facade
Rpc::method('math', MathController::class);

// Or using the RPCRouter facade directly
RPCRouter::addMethod(new MathController());
```

## Configuration

The package includes a basic configuration file. You can publish it with:

```bash
php artisan vendor:publish --provider="JSONRPC\Providers\JSONRPCServiceProvider"
```

The configuration file `config/rpc.php` allows you to customize:

```php
<?php

return [
    'registration' => [
        'driver' => 'default',
        'drivers' => [
            'default' => [
                // Driver-specific configuration
            ],
        ]
    ]
];
```

## API Reference

### RPCRequest

```php
new RPCRequest(
    method: string,           // Required: The method to call
    params: ?array = null,    // Optional: Method parameters
    id: string|int|null = null // Optional: Request ID (null for notifications)
)
```

### RPCResponse

```php
new RPCResponse(
    id: string|int,                    // Required: Request ID
    result: string|array|null = null,  // Optional: Success result
    error: ?RPCErrorObject = null      // Optional: Error object
)
```

**Note**: A response must contain either a `result` or an `error`, but not both.

### RPCNotification

```php
new RPCNotification(
    method: string,        // Required: The method to call
    params: ?array = null  // Optional: Method parameters
)
```

### RPCErrorObject

```php
new RPCErrorObject(
    code: RPCErrorCode,  // Required: Error code
    message: string,     // Required: Error message
    data: mixed = null   // Optional: Additional error data
)
```

### Error Codes

| Code | Constant | Description |
|------|----------|-------------|
| -32700 | PARSE_ERROR | Invalid JSON was received |
| -32600 | INVALID_REQUEST | The JSON sent is not a valid Request object |
| -32601 | METHOD_NOT_FOUND | The method does not exist / is not available |
| -32602 | INVALID_PARAMS | Invalid method parameter(s) |
| -32603 | INTERNAL_ERROR | Internal JSON-RPC error |
| -32000 | SERVER_ERROR | Server error |

## Method Routing

The package supports hierarchical method routing:

- `math` → `MathController::handle()`
- `math/add` → `MathController::add()`
- `weather/current` → `WeatherController::current()`
- `api/v1/calculator` → `MathController::handle()`
- `api/v1/calculator/add` → `MathController::add()`

## Artisan Commands

### List Registered Methods

You can view all registered RPC methods using the provided Artisan command:

```bash
php artisan method:list
```

This will display a formatted list of all registered methods and their corresponding controllers:

```
  math ......................... App\Http\Controllers\RPC\MathController
  weather ..................... App\Http\Controllers\RPC\WeatherController  
  api/v1/calculator ........... App\Http\Controllers\RPC\MathController

        Showing [3] RPC methods
```

This command is useful for:
- Debugging method registration issues
- Viewing the current state of your RPC routing
- Documenting available methods for your team

## Testing

```bash
# Run tests
composer test

# Run tests with coverage
composer test-coverage
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## About Project Saturn Studios

This package is developed and maintained by [Project Saturn Studios](https://projectsaturnstudios.com).

---

**Need help?** Check out the [examples](examples/) directory for more usage examples, or create an issue if you run into any problems!
