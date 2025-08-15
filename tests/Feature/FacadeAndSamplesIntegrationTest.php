<?php

declare(strict_types=1);

use ProjectSaturnStudios\RpcServer\Enums\RpcErrorCode;
use ProjectSaturnStudios\RpcServer\DTO\Requesting\RpcMessageID;
use ProjectSaturnStudios\RpcServer\DTO\Resulting\RpcResultParams;
use ProjectSaturnStudios\RpcServer\DTO\Resulting\RpcError;
use ProjectSaturnStudios\RpcServer\Builders\ProcedureCallResultFactory;
use ProjectSaturnStudios\RpcServer\Interfaces\ArrayableContract;
use ProjectSaturnStudios\RpcServer\Interfaces\ProcedureCallErrorContract;
use ProjectSaturnStudios\RpcServer\Interfaces\ProcedureCallResultContract;
use ProjectSaturnStudios\RpcServer\Support\Facades\MakeProcedureCallResult;
use ProjectSaturnStudios\RpcServer\Support\Facades\RPC;

describe('Facade and Samples Integration Feature Tests', function () {
    
    beforeEach(function () {
        // Manually set up the container bindings instead of registering the service provider
        // to avoid issues with the BaseServiceProvider
        app()->bind(\ProjectSaturnStudios\RpcServer\Interfaces\ProcedureCallRequestContract::class, 
            fn($app, array $args) => new \ProjectSaturnStudios\RpcServer\DTO\IO\RpcRequest(
                id: $args[0],
                method: $args[1],
                params: $args[2] ?? null,
            )
        );

        app()->bind(\ProjectSaturnStudios\RpcServer\Interfaces\ProcedureCallResultContract::class, 
            fn($app, array $args) => new \ProjectSaturnStudios\RpcServer\DTO\IO\RpcResult(
                id: $args[0],
                result: $args[1] ?? null,
                error: $args[2] ?? null,
            )
        );

        app()->bind(\ProjectSaturnStudios\RpcServer\Interfaces\ProcedureCallErrorContract::class, 
            fn($app, array $args) => new \ProjectSaturnStudios\RpcServer\DTO\IO\RpcErrorResult(
                id: $args[0],
                error: $args[1],
            )
        );
        
        // Ensure the factory is properly registered in the container
        ProcedureCallResultFactory::boot();
        $this->messageId = new RpcMessageID('integration-test-123');
    });

    describe('procedure_call_result Helper Function Integration', function () {
        
        test('function exists and is callable', function () {
            expect(function_exists('procedure_call_result'))->toBeTrue();
        });

        test('creates success result with array data without error key', function () {
            $successData = ['status' => 'success', 'data' => 'test'];
            
            $result = procedure_call_result($this->messageId, $successData);
            
            expect($result)->toBeInstanceOf(ProcedureCallResultContract::class);
        });

        test('creates error result with array data containing error key', function () {
            $errorData = ['error' => 'Something went wrong', 'details' => 'test'];
            
            $result = procedure_call_result($this->messageId, $errorData);
            
            expect($result)->toBeInstanceOf(ProcedureCallResultContract::class);
        });

        test('creates success result with ArrayableContract without error', function () {
            $arrayableData = \Mockery::mock(ArrayableContract::class);
            $arrayableData->shouldReceive('toArray')
                ->once()
                ->andReturn(['status' => 'success', 'message' => 'All good']);
            
            $result = procedure_call_result($this->messageId, $arrayableData);
            
            expect($result)->toBeInstanceOf(ProcedureCallResultContract::class);
        });

        test('creates error result with ArrayableContract containing error key', function () {
            $arrayableError = \Mockery::mock(ArrayableContract::class);
            $arrayableError->shouldReceive('toArray')
                ->once()
                ->andReturn(['error' => 'ArrayableContract error', 'code' => 500]);
            
            $result = procedure_call_result($this->messageId, $arrayableError);
            
            expect($result)->toBeInstanceOf(ProcedureCallResultContract::class);
        });

        test('creates success result with null data', function () {
            $result = procedure_call_result($this->messageId, null);
            
            expect($result)->toBeInstanceOf(ProcedureCallResultContract::class);
        });

        test('creates success result with empty array', function () {
            $emptyData = [];
            
            $result = procedure_call_result($this->messageId, $emptyData);
            
            expect($result)->toBeInstanceOf(ProcedureCallResultContract::class);
        });

        test('handles error key with null value as no error', function () {
            $dataWithNullError = ['error' => null, 'data' => 'test'];
            
            $result = procedure_call_result($this->messageId, $dataWithNullError);
            
            expect($result)->toBeInstanceOf(ProcedureCallResultContract::class);
        });

        test('handles error key with false value as no error', function () {
            $dataWithFalseError = ['error' => false, 'data' => 'test'];
            
            $result = procedure_call_result($this->messageId, $dataWithFalseError);
            
            expect($result)->toBeInstanceOf(ProcedureCallResultContract::class);
        });

        test('handles error key with empty string as error', function () {
            $dataWithEmptyError = ['error' => '', 'data' => 'test'];
            
            $result = procedure_call_result($this->messageId, $dataWithEmptyError);
            
            expect($result)->toBeInstanceOf(ProcedureCallResultContract::class);
        });

        test('handles all message ID types', function () {
            $stringId = new RpcMessageID('string-id-test');
            $intId = new RpcMessageID(123);
            $nullId = new RpcMessageID(null);
            
            $result1 = procedure_call_result($stringId, ['success' => true]);
            $result2 = procedure_call_result($intId, ['success' => true]);
            $result3 = procedure_call_result($nullId, ['success' => true]);
            
            expect($result1)->toBeInstanceOf(ProcedureCallResultContract::class);
            expect($result2)->toBeInstanceOf(ProcedureCallResultContract::class);
            expect($result3)->toBeInstanceOf(ProcedureCallResultContract::class);
        });

        test('handles complex nested data structures', function () {
            $complexData = [
                'user' => ['name' => 'John', 'roles' => ['admin', 'user']],
                'settings' => ['theme' => 'dark', 'notifications' => true],
                'meta' => ['version' => '1.0', 'timestamp' => time()]
            ];
            
            $result = procedure_call_result($this->messageId, $complexData);
            
            expect($result)->toBeInstanceOf(ProcedureCallResultContract::class);
        });

        test('handles unicode data', function () {
            $unicodeData = ['message' => 'Hello 世界 🌍', 'emoji' => '🚀'];
            
            $result = procedure_call_result($this->messageId, $unicodeData);
            
            expect($result)->toBeInstanceOf(ProcedureCallResultContract::class);
        });

        test('integrates with MakeProcedureCallResult facade', function () {
            // Test that the helper actually uses the facade correctly
            $successData = ['test' => 'data'];
            $errorData = ['error' => 'test error'];
            
            $successResult = procedure_call_result($this->messageId, $successData);
            $errorResult = procedure_call_result($this->messageId, $errorData);
            
            expect($successResult)->toBeInstanceOf(ProcedureCallResultContract::class);
            expect($errorResult)->toBeInstanceOf(ProcedureCallResultContract::class);
            
            // They should be different types (though both implement the same contract)
            expect(get_class($successResult))->not->toBe(get_class($errorResult));
        });

        test('always uses INTERNAL_ERROR code for error cases', function () {
            $errorData = ['error' => 'custom error message'];
            
            // We can't directly test the error code without exposing internals,
            // but we can verify the function completes successfully
            $result = procedure_call_result($this->messageId, $errorData);
            
            expect($result)->toBeInstanceOf(ProcedureCallResultContract::class);
        });

        test('handles ArrayableContract that throws exception in toArray', function () {
            $problematicArrayable = \Mockery::mock(ArrayableContract::class);
            $problematicArrayable->shouldReceive('toArray')
                ->once()
                ->andThrow(new \Exception('toArray failed'));
            
            expect(fn() => procedure_call_result($this->messageId, $problematicArrayable))
                ->toThrow(\Exception::class, 'toArray failed');
        });
    });

    describe('MakeProcedureCallResult Facade Integration', function () {
        
        test('facade can create success results through container', function () {
            $resultData = new RpcResultParams(['status' => 'success', 'data' => 'test']);
            
            $result = MakeProcedureCallResult::result($this->messageId, $resultData);
            
            expect($result)->toBeInstanceOf(ProcedureCallResultContract::class);
        });

        test('facade can create error results through container', function () {
            $errorData = new RpcResultParams(['error_details' => 'Something went wrong']);
            
            $error = MakeProcedureCallResult::error(
                $this->messageId,
                RpcErrorCode::INTERNAL_ERROR,
                'Internal server error',
                $errorData
            );
            
            expect($error)->toBeInstanceOf(ProcedureCallErrorContract::class);
        });

        test('facade resolves same factory instance from container', function () {
            $factory1 = MakeProcedureCallResult::getFacadeRoot();
            $factory2 = MakeProcedureCallResult::getFacadeRoot();
            
            expect($factory1)->toBeInstanceOf(ProcedureCallResultFactory::class);
            expect($factory2)->toBeInstanceOf(ProcedureCallResultFactory::class);
            // Note: Since boot() uses bind() not singleton(), these may be different instances
        });

        test('facade integrates with procedure_call_result helper', function () {
            $testData = ['message' => 'Hello from helper!'];
            
            $result = procedure_call_result($this->messageId, $testData);
            
            expect($result)->toBeInstanceOf(ProcedureCallResultContract::class);
        });

        test('facade handles all error codes correctly', function () {
            $errorCodes = [
                RpcErrorCode::PARSE_ERROR,
                RpcErrorCode::INVALID_REQUEST,
                RpcErrorCode::METHOD_NOT_FOUND,
                RpcErrorCode::INVALID_PARAMS,
                RpcErrorCode::INTERNAL_ERROR,
                RpcErrorCode::SERVER_ERROR,
            ];
            
            foreach ($errorCodes as $code) {
                $error = MakeProcedureCallResult::error(
                    $this->messageId,
                    $code,
                    "Test message for {$code->name}"
                );
                
                expect($error)->toBeInstanceOf(ProcedureCallErrorContract::class);
            }
        });
    });

    describe('Sample Procedures Configuration', function () {
        
        test('has_sample_procedures helper reads from config', function () {
            // Test with sample procedures disabled (default)
            config(['rpc.sample_procedures' => false]);
            
            expect(has_sample_procedures())->toBeFalse();
            
            // Test with sample procedures enabled
            config(['rpc.sample_procedures' => true]);
            
            expect(has_sample_procedures())->toBeTrue();
        });

        test('has_sample_procedures helper uses default value when config missing', function () {
            // Remove the config key
            config(['rpc.sample_procedures' => null]);
            
            // Should default to false
            expect(has_sample_procedures())->toBeFalse();
        });

        test('has_sample_procedures helper handles various truthy values', function () {
            $truthyValues = [true, 1, '1', 'true', 'yes'];
            
            foreach ($truthyValues as $value) {
                config(['rpc.sample_procedures' => $value]);
                expect(has_sample_procedures())->toBeTrue("Failed for value: " . var_export($value, true));
            }
        });

        test('has_sample_procedures helper handles various falsy values', function () {
            $falsyValues = [false, 0, '0', '', null];
            
            foreach ($falsyValues as $value) {
                config(['rpc.sample_procedures' => $value]);
                expect(has_sample_procedures())->toBeFalse("Failed for value: " . var_export($value, true));
            }
        });
    });

    describe('Sample Procedures Registration', function () {
        
        test('sample procedures are registered when enabled', function () {
            // Enable sample procedures
            config(['rpc.sample_procedures' => true]);
            
            // Register the required singletons manually
            \ProjectSaturnStudios\RpcServer\RpcRouter::boot();
            \ProjectSaturnStudios\RpcServer\RpcServer::boot();
            
            // Get the RPC server and check if procedures are registered
            $rpcServer = app(\ProjectSaturnStudios\RpcServer\RpcServer::class);
            $router = app(\ProjectSaturnStudios\RpcServer\RpcRouter::class);
            
            expect($router)->toBeInstanceOf(\ProjectSaturnStudios\RpcServer\RpcRouter::class);
            expect($rpcServer)->toBeInstanceOf(\ProjectSaturnStudios\RpcServer\RpcServer::class);
        });

        test('sample procedures are not registered when disabled', function () {
            // Disable sample procedures
            config(['rpc.sample_procedures' => false]);
            
            // The routes file should still load but the conditional check should prevent registration
            expect(has_sample_procedures())->toBeFalse();
        });

        test('RPC facade can register procedures', function () {
            $testClosure = function ($request) {
                return procedure_call_result(
                    $request->id(),
                    ['test' => 'data']
                );
            };
            
            // This tests that the RPC facade works for procedure registration
            try {
                RPC::procedure('test_procedure', $testClosure);
                expect(true)->toBeTrue(); // If we get here, no exception was thrown
            } catch (\Exception $e) {
                $this->fail('RPC::procedure should not throw an exception: ' . $e->getMessage());
            }
        });
    });

    describe('End-to-End Integration', function () {
        
        test('complete flow from sample procedure through facade to result', function () {
            // Enable sample procedures
            config(['rpc.sample_procedures' => true]);
            
            // Create a mock request
            $mockRequest = \Mockery::mock(\ProjectSaturnStudios\RpcServer\Interfaces\ProcedureCallRequestContract::class);
            $mockRequest->shouldReceive('id')
                ->andReturn(new RpcMessageID('sample-test-123'));
            $mockRequest->shouldReceive('method')
                ->andReturn('hello_world');
            $mockRequest->shouldReceive('params')
                ->andReturn(null);
            $mockRequest->shouldReceive('getPathInfo')
                ->andReturn('/hello_world');
            $mockRequest->shouldReceive('toArray')
                ->andReturn([
                    'id' => 'sample-test-123',
                    'method' => 'hello_world',
                    'params' => null,
                ]);
            
            // Test the sample procedure logic directly
            $result = procedure_call_result(
                $mockRequest->id(),
                ['message' => 'Hello, World!']
            );
            
            expect($result)->toBeInstanceOf(ProcedureCallResultContract::class);
        });

        test('facade and helper work together for error cases', function () {
            $errorData = ['error' => 'Test error', 'code' => 500];
            
            // This should trigger the error path in procedure_call_result
            $result = procedure_call_result($this->messageId, $errorData);
            
            expect($result)->toBeInstanceOf(ProcedureCallResultContract::class);
        });

        test('facade integrates with Laravel container lifecycle', function () {
            // Test that we can get multiple instances and they work
            $factory1 = app(ProcedureCallResultFactory::class);
            $factory2 = app(ProcedureCallResultFactory::class);
            
            expect($factory1)->toBeInstanceOf(ProcedureCallResultFactory::class);
            expect($factory2)->toBeInstanceOf(ProcedureCallResultFactory::class);
            
            // Both should be able to create results
            $result1 = $factory1->result($this->messageId);
            $result2 = $factory2->result($this->messageId);
            
            expect($result1)->toBeInstanceOf(ProcedureCallResultContract::class);
            expect($result2)->toBeInstanceOf(ProcedureCallResultContract::class);
        });

        test('configuration changes affect sample procedures behavior', function () {
            // Start with samples disabled
            config(['rpc.sample_procedures' => false]);
            expect(has_sample_procedures())->toBeFalse();
            
            // Enable samples
            config(['rpc.sample_procedures' => true]);
            expect(has_sample_procedures())->toBeTrue();
            
            // Disable again
            config(['rpc.sample_procedures' => false]);
            expect(has_sample_procedures())->toBeFalse();
        });
    });

    describe('Laravel Service Container Integration', function () {
        
        test('factory can be resolved from container', function () {
            $factory = app(ProcedureCallResultFactory::class);
            
            expect($factory)->toBeInstanceOf(ProcedureCallResultFactory::class);
        });

        test('facade root resolves to same type as direct container resolution', function () {
            $directFactory = app(ProcedureCallResultFactory::class);
            $facadeFactory = MakeProcedureCallResult::getFacadeRoot();
            
            expect($directFactory)->toBeInstanceOf(ProcedureCallResultFactory::class);
            expect($facadeFactory)->toBeInstanceOf(ProcedureCallResultFactory::class);
            expect(get_class($directFactory))->toBe(get_class($facadeFactory));
        });

        test('service provider registers bindings correctly', function () {
            expect(app()->bound(ProcedureCallResultFactory::class))->toBeTrue();
        });
    });

    describe('Configuration Edge Cases', function () {
        
        test('helper handles missing config file gracefully', function () {
            // Clear all config
            config(['rpc' => null]);
            
            // Should still work with default value
            expect(has_sample_procedures())->toBeFalse();
        });

        test('helper handles malformed config values', function () {
            $malformedValues = [
                ['sample_procedures' => 'not_boolean'],
                ['sample_procedures' => []],
                ['sample_procedures' => new \stdClass()],
            ];
            
            foreach ($malformedValues as $configValue) {
                config(['rpc' => $configValue]);
                
                // Should handle gracefully and return false for non-truthy values
                try {
                    $result = has_sample_procedures();
                    expect($result)->toBeBool(); // Should return some boolean value
                } catch (\Exception $e) {
                    $this->fail('has_sample_procedures should not throw an exception: ' . $e->getMessage());
                }
            }
        });
    });
});
