# JSON-RPC Server for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/projectsaturnstudios/json-rpc.svg?style=flat-square)](https://packagist.org/packages/projectsaturnstudios/json-rpc)
[![Total Downloads](https://img.shields.io/packagist/dt/projectsaturnstudios/json-rpc.svg?style=flat-square)](https://packagist.org/packages/projectsaturnstudios/json-rpc)
[![Code Coverage](https://img.shields.io/badge/coverage-39.8%25-orange?style=flat-square)](tests)

---

## Introduction

This package is designed to implement the JSON-RPC 2.0 Server protocol by registering Procedure Calls 
like Laravel Controller Routes.

## Installation

To get started, install Laravel MCP via the Composer package manager:

```bash
composer require projectsaturnstudios/json-rpc
```

## Quickstart
```php

use ProjectSaturnStudios\RpcServer\Support\Facades\RPC;
use ProjectSaturnStudios\RpcServer\DTO\IO\RpcRequest;

$request = new RpcRequest($id, $method, $params)
$results = RPC::dispatch($request);

```

## Register Procedures
Next, register your procedures in `routes/web.php`:

```php
use ProjectSaturnStudios\RpcServer\Support\Facades\RPC;

RPC::procedure('some_method', SomeProcedureClass::class);
RPC::procedure('some_method', SomeProcedureClass::class."@index");
RPC::prefix('notifications')->group(function() {
    RPC::procedure('some_method', SomeProcedureClass::class); //registers as notifications/some_method
});

```

All Procedures can accept middleware
```php
use ProjectSaturnStudios\RpcServer\Support\Facades\RPC;

RPC::procedure('some_method', SomeProcedureClass::class)->middleware([SomeMiddleware::class]);
RPC::procedure('some_method', SomeProcedureClass::class."@index")->middleware([SomeMiddleware::class]);
RPC::prefix('notifications')->group(function() {
    RPC::procedure('some_method', SomeProcedureClass::class); //registers as notifications/some_method
})->middleware([SomeMiddleware::class]);
```

## Commands
```bash
# View all registered procedure calls
php artisan prodecure:list 

# Generates a new procedure class in App\Rpc\Procedures
php artisan make:procedure SomeProcedure

```

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

This package is developed and maintained with ADHD by [Project Saturn Studios](https://projectsaturnstudios.com).
