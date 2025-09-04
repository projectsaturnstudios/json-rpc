# Contributing to Superconductor RPC

First off, thank you for considering contributing to Superconductor RPC! 🎉

We love your input! We want to make contributing to this project as easy and transparent as possible, whether it's:

- Reporting a bug
- Discussing the current state of the code
- Submitting a fix
- Proposing new features
- Becoming a maintainer

## Development Process

We use GitHub to host code, to track issues and feature requests, as well as accept pull requests.

1. Fork the repo and create your branch from `main`
2. If you've added code that should be tested, add tests
3. If you've changed APIs, update the documentation
4. Ensure the test suite passes
5. Make sure your code lints
6. Issue that pull request!

## Development Setup

### Prerequisites

- PHP 8.2 or higher
- Composer
- Laravel 10.0, 11.0, or 12.0

### Local Development

1. **Clone the repository:**
   ```bash
   git clone https://github.com/projectsaturnstudios/superconductor-rpc.git
   cd superconductor-rpc
   ```

2. **Install dependencies:**
   ```bash
   composer install
   ```

3. **Run tests:**
   ```bash
   composer test
   ```

4. **Run static analysis:**
   ```bash
   ./vendor/bin/phpstan analyse
   ```

5. **Check code style:**
   ```bash
   ./vendor/bin/pint --test
   ```

6. **Fix code style issues:**
   ```bash
   ./vendor/bin/pint
   ```

## Pull Request Process

1. **Update the README.md** with details of changes to the interface, if applicable
2. **Update the CHANGELOG.md** with a note describing your changes
3. **The PR title** should follow the [Conventional Commits](https://www.conventionalcommits.org/) format:
   - `feat:` for new features
   - `fix:` for bug fixes
   - `docs:` for documentation
   - `style:` for style changes
   - `refactor:` for refactoring
   - `test:` for tests
   - `chore:` for maintenance

4. **Ensure all tests pass** and add new tests for new features
5. **Update documentation** if you're changing the API or adding features

### Example PR Title
```
feat: add batch request support for JSON-RPC 2.0
```

## Code Style

This project uses [Laravel Pint](https://github.com/laravel/pint) for code formatting. Please ensure your code follows the PSR-12 standard.

### Running Code Quality Checks

```bash
# Check code style
./vendor/bin/pint --test

# Fix code style issues
./vendor/bin/pint

# Run static analysis
./vendor/bin/phpstan analyse

# Run tests with coverage
composer test-coverage
```

## Testing

### Test Structure

- **Unit tests** in `tests/Unit/` - test individual classes and methods
- **Feature tests** in `tests/Feature/` - test the full request/response cycle
- **Integration tests** in `tests/Integration/` - test with Laravel framework integration

### Writing Tests

```php
<?php

use Superconductor\Rpc\Support\Facades\RPC;
use Superconductor\Rpc\DTO\Messages\RpcMessage;

it('can execute a simple RPC call', function () {
    RPC::method('test/method', TestProcedure::class);

    $request = RpcMessage::fromJsonRpc([
        'jsonrpc' => '2.0',
        'method' => 'test/method',
        'params' => ['value' => 42],
        'id' => 1
    ]);

    $response = RPC::call($request);

    expect($response)->toBeInstanceOf(RpcResult::class);
    expect($response->result)->toBe(42);
});
```

### Test Coverage

We aim for high test coverage. Please ensure your contributions include appropriate tests:

```bash
# Run tests with coverage
composer test-coverage

# Generate HTML coverage report
composer test-coverage-html
```

## Documentation

### Code Documentation

All public methods and classes should have proper PHPDoc comments:

```php
/**
 * Execute an RPC method with the given parameters.
 *
 * @param  string  $method  The RPC method name
 * @param  array  $params  The method parameters
 * @return RpcResult|RpcError
 */
public function call(string $method, array $params = []): RpcResult|RpcError
{
    // Implementation
}
```

### README Updates

When adding new features or changing APIs:

1. Update the relevant section in `README.md`
2. Add code examples where appropriate
3. Update badges if necessary

## Architecture Guidelines

### Design Principles

1. **Type Safety**: Use PHP 8+ features (enums, union types, readonly properties)
2. **Laravel Conventions**: Follow Laravel's patterns and conventions
3. **Clean Architecture**: Separate concerns with clear boundaries
4. **Testability**: Write code that is easy to test
5. **Performance**: Consider performance implications of changes

### Code Organization

- **DTOs** in `src/DTO/` - Data Transfer Objects for requests/responses
- **Core Logic** in `src/` - Main business logic
- **Support Classes** in `src/Support/` - Facades, attributes, etc.
- **Tests** in `tests/` - Organized by type (Unit, Feature, Integration)

### Naming Conventions

- Use descriptive names for variables, methods, and classes
- Follow PSR-4 autoloading standards
- Use camelCase for methods and variables
- Use PascalCase for classes and enums

## Security

If you discover a security vulnerability, please email security@projectsaturnstudios.com instead of creating a public issue.

## Community

- **Discussions**: Use GitHub Discussions for questions and general discussion
- **Issues**: Use GitHub Issues for bugs and feature requests
- **Discord**: Join our [Discord community](https://discord.gg/projectsaturnstudios) for real-time chat

## License

By contributing to Superconductor RPC, you agree that your contributions will be licensed under the MIT License.

---

Thank you for your contribution! 🚀

*This contributing guide is inspired by the [Laravel contribution guidelines](https://laravel.com/docs/contributions) and the [Spatie contribution guidelines](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md).*"
