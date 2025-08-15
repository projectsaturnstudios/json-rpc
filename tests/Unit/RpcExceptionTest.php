<?php

declare(strict_types=1);

use ProjectSaturnStudios\RpcServer\Enums\RpcErrorCode;
use ProjectSaturnStudios\RpcServer\Exceptions\RpcException;
use ProjectSaturnStudios\RpcServer\Interfaces\RpcExceptionContract;

describe('RpcException Unit Tests', function () {

    describe('basic instantiation', function () {
        
        test('can create with required status code parameter', function () {
            $exception = new RpcException(RpcErrorCode::INTERNAL_ERROR);
            
            expect($exception)->toBeInstanceOf(RpcException::class);
            expect($exception)->toBeInstanceOf(\RuntimeException::class);
            expect($exception)->toBeInstanceOf(RpcExceptionContract::class);
        });

        test('can create with all parameters', function () {
            $previous = new \Exception('Previous exception');
            $exception = new RpcException(
                RpcErrorCode::INVALID_REQUEST,
                'Custom error message',
                $previous,
                123
            );
            
            expect($exception)->toBeInstanceOf(RpcException::class);
            expect($exception->getMessage())->toBe('Custom error message');
            expect($exception->getCode())->toBe(123);
            expect($exception->getPrevious())->toBe($previous);
        });

        test('constructor parameters are properly typed', function () {
            $exception = new RpcException(
                statusCode: RpcErrorCode::PARSE_ERROR,
                message: 'Test message',
                previous: null,
                code: 0
            );
            
            expect($exception)->toBeInstanceOf(RpcException::class);
        });
    });

    describe('inheritance and interfaces', function () {
        
        test('extends RuntimeException', function () {
            $exception = new RpcException(RpcErrorCode::SERVER_ERROR);
            
            expect($exception)->toBeInstanceOf(\RuntimeException::class);
            expect($exception)->toBeInstanceOf(\Exception::class);
        });

        test('implements RpcExceptionContract', function () {
            $exception = new RpcException(RpcErrorCode::METHOD_NOT_FOUND);
            
            expect($exception)->toBeInstanceOf(RpcExceptionContract::class);
        });

        test('is throwable', function () {
            $exception = new RpcException(RpcErrorCode::INVALID_PARAMS);
            
            expect($exception)->toBeInstanceOf(\Throwable::class);
        });
    });

    describe('status code handling', function () {
        
        test('getStatusCode returns enum value', function () {
            $exception = new RpcException(RpcErrorCode::INTERNAL_ERROR);
            
            expect($exception->getStatusCode())->toBe(RpcErrorCode::INTERNAL_ERROR->value);
        });

        test('handles all RPC error codes', function () {
            $errorCodes = [
                RpcErrorCode::PARSE_ERROR,
                RpcErrorCode::INVALID_REQUEST,
                RpcErrorCode::METHOD_NOT_FOUND,
                RpcErrorCode::INVALID_PARAMS,
                RpcErrorCode::INTERNAL_ERROR,
                RpcErrorCode::SERVER_ERROR,
            ];
            
            foreach ($errorCodes as $code) {
                $exception = new RpcException($code);
                expect($exception->getStatusCode())->toBe($code->value);
            }
        });

        test('status code is private property', function () {
            $exception = new RpcException(RpcErrorCode::PARSE_ERROR);
            
            $reflection = new ReflectionClass($exception);
            $property = $reflection->getProperty('statusCode');
            
            expect($property->isPrivate())->toBeTrue();
        });
    });

    describe('exception message handling', function () {
        
        test('default message is empty string', function () {
            $exception = new RpcException(RpcErrorCode::INTERNAL_ERROR);
            
            expect($exception->getMessage())->toBe('');
        });

        test('custom message is preserved', function () {
            $message = 'This is a custom error message';
            $exception = new RpcException(RpcErrorCode::INVALID_REQUEST, $message);
            
            expect($exception->getMessage())->toBe($message);
        });

        test('handles unicode in messages', function () {
            $message = 'Error message with unicode: 世界 🌍';
            $exception = new RpcException(RpcErrorCode::SERVER_ERROR, $message);
            
            expect($exception->getMessage())->toBe($message);
        });

        test('handles very long messages', function () {
            $message = str_repeat('Error message ', 100);
            $exception = new RpcException(RpcErrorCode::PARSE_ERROR, $message);
            
            expect($exception->getMessage())->toBe($message);
        });
    });

    describe('exception code handling', function () {
        
        test('default code is zero', function () {
            $exception = new RpcException(RpcErrorCode::INTERNAL_ERROR);
            
            expect($exception->getCode())->toBe(0);
        });

        test('custom code is preserved', function () {
            $exception = new RpcException(RpcErrorCode::INVALID_PARAMS, 'Test', null, 12345);
            
            expect($exception->getCode())->toBe(12345);
        });

        test('negative codes are allowed', function () {
            $exception = new RpcException(RpcErrorCode::SERVER_ERROR, 'Test', null, -500);
            
            expect($exception->getCode())->toBe(-500);
        });
    });

    describe('previous exception handling', function () {
        
        test('default previous is null', function () {
            $exception = new RpcException(RpcErrorCode::INTERNAL_ERROR);
            
            expect($exception->getPrevious())->toBeNull();
        });

        test('previous exception is preserved', function () {
            $previous = new \Exception('Previous exception');
            $exception = new RpcException(RpcErrorCode::METHOD_NOT_FOUND, 'Test', $previous);
            
            expect($exception->getPrevious())->toBe($previous);
        });

        test('previous can be any Throwable', function () {
            $previous = new \RuntimeException('Runtime error');
            $exception = new RpcException(RpcErrorCode::INVALID_REQUEST, 'Test', $previous);
            
            expect($exception->getPrevious())->toBe($previous);
            expect($exception->getPrevious())->toBeInstanceOf(\RuntimeException::class);
        });

        test('exception chaining works', function () {
            $first = new \Exception('First exception');
            $second = new \RuntimeException('Second exception', 0, $first);
            $rpcException = new RpcException(RpcErrorCode::INTERNAL_ERROR, 'RPC Exception', $second);
            
            expect($rpcException->getPrevious())->toBe($second);
            expect($rpcException->getPrevious()->getPrevious())->toBe($first);
        });
    });

    describe('throwable behavior', function () {
        
        test('can be thrown and caught', function () {
            expect(function () {
                throw new RpcException(RpcErrorCode::PARSE_ERROR, 'Test exception');
            })->toThrow(RpcException::class, 'Test exception');
        });

        test('can be caught as RuntimeException', function () {
            try {
                throw new RpcException(RpcErrorCode::INVALID_PARAMS, 'Runtime test');
            } catch (\RuntimeException $e) {
                expect($e)->toBeInstanceOf(RpcException::class);
                expect($e->getMessage())->toBe('Runtime test');
            }
        });

        test('can be caught as RpcExceptionContract', function () {
            try {
                throw new RpcException(RpcErrorCode::SERVER_ERROR, 'Contract test');
            } catch (RpcExceptionContract $e) {
                expect($e)->toBeInstanceOf(RpcException::class);
                expect($e->getStatusCode())->toBe(RpcErrorCode::SERVER_ERROR->value);
            }
        });

        test('stack trace is preserved', function () {
            $exception = new RpcException(RpcErrorCode::INTERNAL_ERROR, 'Stack trace test');
            
            expect($exception->getTrace())->toBeArray();
            expect($exception->getTraceAsString())->toBeString();
            expect($exception->getFile())->toBeString();
            expect($exception->getLine())->toBeInt();
        });
    });

    describe('constructor property promotion', function () {
        
        test('uses constructor property promotion for statusCode', function () {
            $reflection = new ReflectionClass(RpcException::class);
            $constructor = $reflection->getConstructor();
            $parameters = $constructor->getParameters();
            
            $statusCodeParam = $parameters[0];
            expect($statusCodeParam->getName())->toBe('statusCode');
            expect($statusCodeParam->isPromoted())->toBeTrue();
        });

        test('statusCode property is accessible via getStatusCode', function () {
            $exception = new RpcException(RpcErrorCode::METHOD_NOT_FOUND);
            
            // We can't access the private property directly, but we can verify it works
            expect($exception->getStatusCode())->toBe(RpcErrorCode::METHOD_NOT_FOUND->value);
        });
    });

    describe('edge cases', function () {
        
        test('handles empty string message explicitly', function () {
            $exception = new RpcException(RpcErrorCode::PARSE_ERROR, '');
            
            expect($exception->getMessage())->toBe('');
        });

        test('handles zero code explicitly', function () {
            $exception = new RpcException(RpcErrorCode::INVALID_REQUEST, 'Test', null, 0);
            
            expect($exception->getCode())->toBe(0);
        });

        test('handles null previous explicitly', function () {
            $exception = new RpcException(RpcErrorCode::SERVER_ERROR, 'Test', null);
            
            expect($exception->getPrevious())->toBeNull();
        });

        test('toString includes class name and message', function () {
            $exception = new RpcException(RpcErrorCode::INTERNAL_ERROR, 'Test error');
            $string = (string) $exception;
            
            expect($string)->toContain('RpcException');
            expect($string)->toContain('Test error');
        });
    });

    describe('comparison with standard exceptions', function () {
        
        test('behaves like RuntimeException with additional status code', function () {
            $runtimeException = new \RuntimeException('Runtime message', 123);
            $rpcException = new RpcException(RpcErrorCode::INTERNAL_ERROR, 'Runtime message', null, 123);
            
            expect($rpcException->getMessage())->toBe($runtimeException->getMessage());
            expect($rpcException->getCode())->toBe($runtimeException->getCode());
            expect($rpcException)->toBeInstanceOf(\RuntimeException::class);
            
            // But RpcException has additional functionality
            expect($rpcException->getStatusCode())->toBe(RpcErrorCode::INTERNAL_ERROR->value);
        });
    });
});
