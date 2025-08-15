<?php

declare(strict_types=1);

use ProjectSaturnStudios\RpcServer\Enums\RpcErrorCode;
use ProjectSaturnStudios\RpcServer\Exceptions\NotFoundRpcException;
use ProjectSaturnStudios\RpcServer\Exceptions\RpcException;
use ProjectSaturnStudios\RpcServer\Interfaces\RpcExceptionContract;

describe('NotFoundRpcException Unit Tests', function () {

    describe('basic instantiation', function () {
        
        test('can create with default parameters', function () {
            $exception = new NotFoundRpcException();
            
            expect($exception)->toBeInstanceOf(NotFoundRpcException::class);
            expect($exception)->toBeInstanceOf(RpcException::class);
            expect($exception)->toBeInstanceOf(\RuntimeException::class);
            expect($exception)->toBeInstanceOf(RpcExceptionContract::class);
        });

        test('can create with message parameter', function () {
            $exception = new NotFoundRpcException('Method not found');
            
            expect($exception)->toBeInstanceOf(NotFoundRpcException::class);
            expect($exception->getMessage())->toBe('Method not found');
        });

        test('can create with all parameters', function () {
            $previous = new \Exception('Previous exception');
            $exception = new NotFoundRpcException('Custom message', $previous, 404);
            
            expect($exception)->toBeInstanceOf(NotFoundRpcException::class);
            expect($exception->getMessage())->toBe('Custom message');
            expect($exception->getCode())->toBe(404);
            expect($exception->getPrevious())->toBe($previous);
        });
    });

    describe('inheritance behavior', function () {
        
        test('extends RpcException', function () {
            $exception = new NotFoundRpcException();
            
            expect($exception)->toBeInstanceOf(RpcException::class);
        });

        test('inherits all parent interfaces', function () {
            $exception = new NotFoundRpcException();
            
            expect($exception)->toBeInstanceOf(RpcExceptionContract::class);
            expect($exception)->toBeInstanceOf(\RuntimeException::class);
            expect($exception)->toBeInstanceOf(\Exception::class);
            expect($exception)->toBeInstanceOf(\Throwable::class);
        });

        test('inherits parent methods', function () {
            $exception = new NotFoundRpcException('Test message');
            
            expect($exception->getMessage())->toBe('Test message');
            expect($exception->getStatusCode())->toBe(RpcErrorCode::METHOD_NOT_FOUND->value);
        });
    });

    describe('status code behavior', function () {
        
        test('always uses METHOD_NOT_FOUND status code', function () {
            $exception = new NotFoundRpcException();
            
            expect($exception->getStatusCode())->toBe(RpcErrorCode::METHOD_NOT_FOUND->value);
        });

        test('status code is consistent regardless of constructor parameters', function () {
            $exception1 = new NotFoundRpcException();
            $exception2 = new NotFoundRpcException('With message');
            $exception3 = new NotFoundRpcException('With all', new \Exception(), 123);
            
            expect($exception1->getStatusCode())->toBe(RpcErrorCode::METHOD_NOT_FOUND->value);
            expect($exception2->getStatusCode())->toBe(RpcErrorCode::METHOD_NOT_FOUND->value);
            expect($exception3->getStatusCode())->toBe(RpcErrorCode::METHOD_NOT_FOUND->value);
        });

        test('status code matches expected RPC error code value', function () {
            $exception = new NotFoundRpcException();
            
            expect($exception->getStatusCode())->toBe(-32601); // METHOD_NOT_FOUND value
        });
    });

    describe('constructor parameter handling', function () {
        
        test('default message is empty string', function () {
            $exception = new NotFoundRpcException();
            
            expect($exception->getMessage())->toBe('');
        });

        test('custom message is preserved', function () {
            $message = 'The requested RPC method was not found';
            $exception = new NotFoundRpcException($message);
            
            expect($exception->getMessage())->toBe($message);
        });

        test('default previous is null', function () {
            $exception = new NotFoundRpcException();
            
            expect($exception->getPrevious())->toBeNull();
        });

        test('custom previous is preserved', function () {
            $previous = new \InvalidArgumentException('Invalid method name');
            $exception = new NotFoundRpcException('Method not found', $previous);
            
            expect($exception->getPrevious())->toBe($previous);
        });

        test('default code is zero', function () {
            $exception = new NotFoundRpcException();
            
            expect($exception->getCode())->toBe(0);
        });

        test('custom code is preserved', function () {
            $exception = new NotFoundRpcException('Test', null, 404);
            
            expect($exception->getCode())->toBe(404);
        });
    });

    describe('parent constructor delegation', function () {
        
        test('calls parent constructor with correct parameters', function () {
            $message = 'Test message';
            $previous = new \Exception('Previous');
            $code = 500;
            
            $exception = new NotFoundRpcException($message, $previous, $code);
            
            // Verify that parent constructor was called correctly
            expect($exception->getMessage())->toBe($message);
            expect($exception->getPrevious())->toBe($previous);
            expect($exception->getCode())->toBe($code);
            expect($exception->getStatusCode())->toBe(RpcErrorCode::METHOD_NOT_FOUND->value);
        });

        test('parent constructor receives METHOD_NOT_FOUND status code', function () {
            // We can verify this by checking that the status code is always METHOD_NOT_FOUND
            // regardless of what other parameters we pass
            $exception = new NotFoundRpcException('Any message', null, 999);
            
            expect($exception->getStatusCode())->toBe(RpcErrorCode::METHOD_NOT_FOUND->value);
        });
    });

    describe('throwable behavior', function () {
        
        test('can be thrown and caught as NotFoundRpcException', function () {
            expect(function () {
                throw new NotFoundRpcException('Method not found');
            })->toThrow(NotFoundRpcException::class, 'Method not found');
        });

        test('can be caught as RpcException', function () {
            try {
                throw new NotFoundRpcException('RPC test');
            } catch (RpcException $e) {
                expect($e)->toBeInstanceOf(NotFoundRpcException::class);
                expect($e->getMessage())->toBe('RPC test');
                expect($e->getStatusCode())->toBe(RpcErrorCode::METHOD_NOT_FOUND->value);
            }
        });

        test('can be caught as RuntimeException', function () {
            try {
                throw new NotFoundRpcException('Runtime test');
            } catch (\RuntimeException $e) {
                expect($e)->toBeInstanceOf(NotFoundRpcException::class);
                expect($e->getMessage())->toBe('Runtime test');
            }
        });

        test('can be caught as RpcExceptionContract', function () {
            try {
                throw new NotFoundRpcException('Contract test');
            } catch (RpcExceptionContract $e) {
                expect($e)->toBeInstanceOf(NotFoundRpcException::class);
                expect($e->getStatusCode())->toBe(RpcErrorCode::METHOD_NOT_FOUND->value);
            }
        });
    });

    describe('real-world usage scenarios', function () {
        
        test('typical RPC method not found scenario', function () {
            $methodName = 'nonexistent.method';
            $message = "RPC method '{$methodName}' not found";
            
            $exception = new NotFoundRpcException($message);
            
            expect($exception->getMessage())->toBe($message);
            expect($exception->getStatusCode())->toBe(RpcErrorCode::METHOD_NOT_FOUND->value);
        });

        test('with previous exception from method resolution', function () {
            $previous = new \BadMethodCallException('Method does not exist');
            $exception = new NotFoundRpcException('RPC method resolution failed', $previous);
            
            expect($exception->getPrevious())->toBe($previous);
            expect($exception->getMessage())->toBe('RPC method resolution failed');
        });

        test('with HTTP status code for REST API integration', function () {
            $exception = new NotFoundRpcException('Method not found', null, 404);
            
            expect($exception->getCode())->toBe(404); // HTTP status
            expect($exception->getStatusCode())->toBe(-32601); // RPC error code
        });
    });

    describe('parameter validation', function () {
        
        test('handles empty string message', function () {
            $exception = new NotFoundRpcException('');
            
            expect($exception->getMessage())->toBe('');
        });

        test('handles unicode in message', function () {
            $message = 'Method 找不到方法 🔍';
            $exception = new NotFoundRpcException($message);
            
            expect($exception->getMessage())->toBe($message);
        });

        test('handles very long messages', function () {
            $message = 'Method not found: ' . str_repeat('very_long_method_name_', 50);
            $exception = new NotFoundRpcException($message);
            
            expect($exception->getMessage())->toBe($message);
        });

        test('handles negative error codes', function () {
            $exception = new NotFoundRpcException('Test', null, -1);
            
            expect($exception->getCode())->toBe(-1);
        });
    });

    describe('comparison with parent class', function () {
        
        test('behaves like RpcException with fixed status code', function () {
            $message = 'Test message';
            $previous = new \Exception('Previous');
            $code = 123;
            
            $notFoundException = new NotFoundRpcException($message, $previous, $code);
            $rpcException = new RpcException(RpcErrorCode::METHOD_NOT_FOUND, $message, $previous, $code);
            
            // Should behave identically except for construction
            expect($notFoundException->getMessage())->toBe($rpcException->getMessage());
            expect($notFoundException->getCode())->toBe($rpcException->getCode());
            expect($notFoundException->getPrevious())->toBe($rpcException->getPrevious());
            expect($notFoundException->getStatusCode())->toBe($rpcException->getStatusCode());
        });

        test('is more convenient than parent for METHOD_NOT_FOUND errors', function () {
            // NotFoundRpcException is simpler to construct
            $notFoundException = new NotFoundRpcException('Method not found');
            
            // Equivalent RpcException requires explicit status code
            $rpcException = new RpcException(RpcErrorCode::METHOD_NOT_FOUND, 'Method not found');
            
            expect($notFoundException->getStatusCode())->toBe($rpcException->getStatusCode());
            expect($notFoundException->getMessage())->toBe($rpcException->getMessage());
        });
    });

    describe('class design validation', function () {
        
        test('class is concrete not abstract', function () {
            $reflection = new ReflectionClass(NotFoundRpcException::class);
            
            expect($reflection->isAbstract())->toBeFalse();
            expect($reflection->isFinal())->toBeFalse();
            expect($reflection->isInstantiable())->toBeTrue();
        });

        test('constructor has correct signature', function () {
            $reflection = new ReflectionClass(NotFoundRpcException::class);
            $constructor = $reflection->getConstructor();
            $parameters = $constructor->getParameters();
            
            expect($parameters)->toHaveLength(3);
            expect($parameters[0]->getName())->toBe('message');
            expect($parameters[1]->getName())->toBe('previous');
            expect($parameters[2]->getName())->toBe('code');
            
            // All parameters should be optional
            expect($parameters[0]->isOptional())->toBeTrue();
            expect($parameters[1]->isOptional())->toBeTrue();
            expect($parameters[2]->isOptional())->toBeTrue();
        });

        test('extends correct parent class', function () {
            $reflection = new ReflectionClass(NotFoundRpcException::class);
            $parent = $reflection->getParentClass();
            
            expect($parent->getName())->toBe(RpcException::class);
        });
    });

    describe('edge cases', function () {
        
        test('toString includes class name and message', function () {
            $exception = new NotFoundRpcException('Method not found');
            $string = (string) $exception;
            
            expect($string)->toContain('NotFoundRpcException');
            expect($string)->toContain('Method not found');
        });

        test('stack trace is preserved', function () {
            $exception = new NotFoundRpcException('Stack trace test');
            
            expect($exception->getTrace())->toBeArray();
            expect($exception->getTraceAsString())->toBeString();
            expect($exception->getFile())->toBeString();
            expect($exception->getLine())->toBeInt();
        });

        test('exception chaining works through inheritance', function () {
            $first = new \Exception('First');
            $second = new \RuntimeException('Second', 0, $first);
            $notFoundException = new NotFoundRpcException('Not found', $second);
            
            expect($notFoundException->getPrevious())->toBe($second);
            expect($notFoundException->getPrevious()->getPrevious())->toBe($first);
        });
    });
});
