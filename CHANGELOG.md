# Changelog

All notable changes to `superconductor/rpc` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.3.0] - 2024-12-XX

### 🎉 Major Rewrite - Complete Architecture Overhaul

This release represents a complete rewrite of the package, transforming it from a basic JSON-RPC implementation into a modern, type-safe Laravel package with extensive ecosystem integration.

#### ✨ Added

- **Full Laravel Integration**: Complete rewrite using Laravel conventions and patterns
- **Type Safety**: Integration with Spatie Laravel Data for strongly-typed request/response objects
- **Laravel Actions Pattern**: Built-in support for organizing procedures using Laravel Actions
- **Modern PHP 8.2+ Features**: Utilizes enums, readonly properties, and modern PHP syntax
- **Comprehensive DTO System**: Separate incoming/outgoing message types with proper serialization
- **Attribute-Based Configuration**: `#[UsesRpcRequest]` attribute for custom request classes
- **Enhanced Error Handling**: Proper JSON-RPC 2.0 error code implementation with enum support
- **Notification Support**: Full support for JSON-RPC notifications (fire-and-forget operations)
- **Reflection-Based Routing**: Automatic method resolution and parameter binding
- **Laravel Service Provider**: Proper Laravel integration with auto-discovery
- **Facade Pattern**: Clean `RPC::method()`, `RPC::call()`, `RPC::notify()` interface
- **Comprehensive Testing**: Full Pest PHP test suite with coverage reporting
- **Documentation**: Extensive inline documentation and usage examples

#### 🔄 Changed

- **Package Name**: Renamed from `projectsaturnstudios/json-rpc` to `superconductor/rpc`
- **Namespace**: Changed from `ProjectSaturnStudios\RpcServer` to `Superconductor\Rpc`
- **Architecture**: Complete rewrite with modern Laravel patterns and type safety
- **Method Registration**: Simplified from `RPC::procedure()` to `RPC::method()`
- **Parameter Binding**: Enhanced with reflection and custom request class support
- **Error Handling**: Improved with proper enum-based error codes and structured responses

#### 🗑️ Removed

- **Legacy Routing System**: Removed complex routing with prefixes and groups
- **Interface Contracts**: Simplified architecture removes complex interface dependencies
- **Command-Line Tools**: Removed Artisan commands (may be added back in future versions)
- **Configuration Files**: Streamlined to use Laravel conventions without custom config files
- **Stubs**: Removed stub files in favor of cleaner, convention-based approach

#### 🐛 Fixed

- **Type Safety**: Resolved all type-related issues with proper DTO implementation
- **Method Resolution**: Fixed method not found errors with improved routing
- **Parameter Validation**: Enhanced parameter validation and error reporting
- **JSON-RPC Compliance**: Ensured full compliance with JSON-RPC 2.0 specification

#### 📚 Documentation

- **Complete Rewrite**: New comprehensive README with usage examples and architecture overview
- **Installation Guide**: Step-by-step installation and setup instructions
- **API Reference**: Detailed API documentation with examples
- **Migration Guide**: Instructions for migrating from the previous version

### Migration Guide

#### From `projectsaturnstudios/json-rpc` (v1.x)

**1. Update Dependencies:**
```bash
composer remove projectsaturnstudios/json-rpc
composer require superconductor/rpc
```

**2. Update Namespaces:**
```php
// Old
use ProjectSaturnStudios\RpcServer\Support\Facades\RPC;
use ProjectSaturnStudios\RpcServer\DTO\IO\RpcRequest;

// New
use Superconductor\Rpc\Support\Facades\RPC;
use Superconductor\Rpc\DTO\Messages\Incoming\RpcRequest;
```

**3. Update Method Registration:**
```php
// Old
RPC::procedure('math/add', MathController::class);

// New
RPC::method('math/add', MathController::class);
```

**4. Update Procedure Classes:**
```php
// Old
class MathController extends RpcProcedure
{
    public function handle(Request $request): JsonResponse
    {
        return response()->json(['result' => $request->a + $request->b]);
    }
}

// New
class MathController extends RpcProcedure
{
    public function handle(array $params): RpcResult|RpcError
    {
        $result = $params['a'] + $params['b'];
        return new RpcResult(request()->get('id'), $result);
    }
}
```

**5. Update Request Handling:**
```php
// Old
$request = new RpcRequest($id, $method, $params);
$results = RPC::dispatch($request);

// New
$message = RpcMessage::fromJsonRpc($jsonString);
$response = RPC::call($message);
```


---

## Previous Versions

For changes in versions prior to 0.3.0, please refer to the [original json-rpc repository](https://github.com/projectsaturnstudios/json-rpc).

---

## Contributing

When contributing to this changelog:

1. **Added** - for new features
2. **Changed** - for changes in existing functionality
3. **Deprecated** - for soon-to-be removed features
4. **Removed** - for now removed features
5. **Fixed** - for any bug fixes
6. **Security** - in case of vulnerabilities

Please follow the [Keep a Changelog](https://keepachangelog.com/en/1.0.0/) format.
