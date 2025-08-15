<?php

declare(strict_types=1);

use Illuminate\Container\Container;
use ProjectSaturnStudios\RpcServer\Builders\ProcedureCallResultFactory;
use ProjectSaturnStudios\RpcServer\Enums\RpcErrorCode;
use ProjectSaturnStudios\RpcServer\DTO\Resulting\RpcError;
use ProjectSaturnStudios\RpcServer\DTO\Requesting\RpcMessageID;
use ProjectSaturnStudios\RpcServer\DTO\Resulting\RpcResultParams;
use ProjectSaturnStudios\RpcServer\Interfaces\ProcedureCallErrorContract;
use ProjectSaturnStudios\RpcServer\Interfaces\ProcedureCallResultContract;
use ProjectSaturnStudios\RpcServer\Interfaces\RpcResultBodyContract;

describe('ProcedureCallResultFactory Unit Tests', function () {
    
    beforeEach(function () {
        $this->container = new Container();
        $this->factory = new ProcedureCallResultFactory($this->container);
    });

    describe('basic instantiation', function () {
        
        test('can create factory with container', function () {
            expect($this->factory)->toBeInstanceOf(ProcedureCallResultFactory::class);
        });
    });

    describe('error result creation', function () {
        
        test('can create error result with required parameters', function () {
            $id = new RpcMessageID('test-123');
            $code = RpcErrorCode::INTERNAL_ERROR;
            $message = 'Something went wrong';
            
            // Mock the container to return a proper error contract
            $mockError = \Mockery::mock(ProcedureCallErrorContract::class);
            $this->container->bind(ProcedureCallErrorContract::class, fn() => $mockError);
            
            $result = $this->factory->error($id, $code, $message);
            
            expect($result)->toBeInstanceOf(ProcedureCallErrorContract::class)
                ->and($result)->toBe($mockError);
        });

        test('can create error result with optional data', function () {
            $id = new RpcMessageID('test-456');
            $code = RpcErrorCode::METHOD_NOT_FOUND;
            $message = 'Method not found';
            $data = \Mockery::mock(RpcResultBodyContract::class);
            
            $mockError = \Mockery::mock(ProcedureCallErrorContract::class);
            $this->container->bind(ProcedureCallErrorContract::class, fn() => $mockError);
            
            $result = $this->factory->error($id, $code, $message, $data);
            
            expect($result)->toBeInstanceOf(ProcedureCallErrorContract::class);
        });

        test('error method creates RpcError with correct parameters', function () {
            $id = new RpcMessageID('error-test');
            $code = RpcErrorCode::INVALID_PARAMS;
            $message = 'Invalid parameters provided';
            $data = \Mockery::mock(RpcResultBodyContract::class);
            
            // Capture the arguments passed to the container
            $capturedArgs = null;
            $this->container->bind(ProcedureCallErrorContract::class, function ($app, $args) use (&$capturedArgs) {
                $capturedArgs = $args;
                return \Mockery::mock(ProcedureCallErrorContract::class);
            });
            
            $this->factory->error($id, $code, $message, $data);
            
            expect($capturedArgs)->toHaveCount(2)
                ->and($capturedArgs[0])->toBe($id)
                ->and($capturedArgs[1])->toBeInstanceOf(RpcError::class);
        });

        test('handles all error codes correctly', function () {
            $id = new RpcMessageID('code-test');
            $message = 'Test message';
            
            $errorCodes = [
                RpcErrorCode::PARSE_ERROR,
                RpcErrorCode::INVALID_REQUEST,
                RpcErrorCode::METHOD_NOT_FOUND,
                RpcErrorCode::INVALID_PARAMS,
                RpcErrorCode::INTERNAL_ERROR,
                RpcErrorCode::SERVER_ERROR,
            ];
            
            $mockError = \Mockery::mock(ProcedureCallErrorContract::class);
            $this->container->bind(ProcedureCallErrorContract::class, fn() => $mockError);
            
            foreach ($errorCodes as $code) {
                $result = $this->factory->error($id, $code, $message);
                expect($result)->toBeInstanceOf(ProcedureCallErrorContract::class);
            }
        });
    });

    describe('success result creation', function () {
        
        test('can create success result with required parameters', function () {
            $id = new RpcMessageID('success-123');
            
            $mockResult = \Mockery::mock(ProcedureCallResultContract::class);
            $this->container->bind(ProcedureCallResultContract::class, fn() => $mockResult);
            
            $result = $this->factory->result($id);
            
            expect($result)->toBeInstanceOf(ProcedureCallResultContract::class)
                ->and($result)->toBe($mockResult);
        });

        test('can create success result with data', function () {
            $id = new RpcMessageID('success-456');
            $data = \Mockery::mock(RpcResultBodyContract::class);
            
            $mockResult = \Mockery::mock(ProcedureCallResultContract::class);
            $this->container->bind(ProcedureCallResultContract::class, fn() => $mockResult);
            
            $result = $this->factory->result($id, $data);
            
            expect($result)->toBeInstanceOf(ProcedureCallResultContract::class);
        });

        test('result method passes correct arguments to container', function () {
            $id = new RpcMessageID('args-test');
            $data = \Mockery::mock(RpcResultBodyContract::class);
            
            $capturedArgs = null;
            $this->container->bind(ProcedureCallResultContract::class, function ($app, $args) use (&$capturedArgs) {
                $capturedArgs = $args;
                return \Mockery::mock(ProcedureCallResultContract::class);
            });
            
            $this->factory->result($id, $data);
            
            expect($capturedArgs)->toHaveCount(3)
                ->and($capturedArgs[0])->toBe($id)
                ->and($capturedArgs[1])->toBe($data)
                ->and($capturedArgs[2])->toBeNull(); // error should be null for success
        });

        test('handles null data correctly', function () {
            $id = new RpcMessageID('null-test');
            
            $capturedArgs = null;
            $this->container->bind(ProcedureCallResultContract::class, function ($app, $args) use (&$capturedArgs) {
                $capturedArgs = $args;
                return \Mockery::mock(ProcedureCallResultContract::class);
            });
            
            $this->factory->result($id, null);
            
            expect($capturedArgs[1])->toBeNull();
        });
    });

    describe('message ID handling', function () {
        
        test('handles string message IDs', function () {
            $id = new RpcMessageID('string-id');
            
            $mockResult = \Mockery::mock(ProcedureCallResultContract::class);
            $this->container->bind(ProcedureCallResultContract::class, fn() => $mockResult);
            
            $result = $this->factory->result($id);
            
            expect($result)->toBeInstanceOf(ProcedureCallResultContract::class);
        });

        test('handles integer message IDs', function () {
            $id = new RpcMessageID(12345);
            
            $mockResult = \Mockery::mock(ProcedureCallResultContract::class);
            $this->container->bind(ProcedureCallResultContract::class, fn() => $mockResult);
            
            $result = $this->factory->result($id);
            
            expect($result)->toBeInstanceOf(ProcedureCallResultContract::class);
        });

        test('handles null message IDs', function () {
            $id = new RpcMessageID(null);
            
            $mockResult = \Mockery::mock(ProcedureCallResultContract::class);
            $this->container->bind(ProcedureCallResultContract::class, fn() => $mockResult);
            
            $result = $this->factory->result($id);
            
            expect($result)->toBeInstanceOf(ProcedureCallResultContract::class);
        });
    });

    describe('container integration', function () {
        
        test('uses container to resolve contracts', function () {
            $id = new RpcMessageID('container-test');
            
            // Verify the container's make method is called
            $containerSpy = \Mockery::spy(Container::class);
            $factory = new ProcedureCallResultFactory($containerSpy);
            
            $containerSpy->shouldReceive('make')
                ->with(ProcedureCallResultContract::class, \Mockery::any())
                ->once()
                ->andReturn(\Mockery::mock(ProcedureCallResultContract::class));
            
            $factory->result($id);
            
            $containerSpy->shouldHaveReceived('make');
        });

        test('passes correct arguments to container for errors', function () {
            $id = new RpcMessageID('container-error-test');
            $code = RpcErrorCode::SERVER_ERROR;
            $message = 'Server error occurred';
            
            $containerSpy = \Mockery::spy(Container::class);
            $factory = new ProcedureCallResultFactory($containerSpy);
            
            $containerSpy->shouldReceive('make')
                ->with(ProcedureCallErrorContract::class, \Mockery::type('array'))
                ->once()
                ->andReturn(\Mockery::mock(ProcedureCallErrorContract::class));
            
            $factory->error($id, $code, $message);
            
            $containerSpy->shouldHaveReceived('make');
        });
    });

    describe('factory registration behavior', function () {
        
        test('factory boots correctly in container', function () {
            // Clear any existing bindings first
            app()->forgetInstance(ProcedureCallResultFactory::class);
            app()->offsetUnset(ProcedureCallResultFactory::class);
            
            ProcedureCallResultFactory::boot();
            
            expect(app()->bound(ProcedureCallResultFactory::class))->toBeTrue();
            
            $factory1 = app(ProcedureCallResultFactory::class);
            $factory2 = app(ProcedureCallResultFactory::class);
            
            // Note: Since boot() uses bind() not singleton(), these will be different instances
            expect($factory1)->not->toBe($factory2);
            expect($factory1)->toBeInstanceOf(ProcedureCallResultFactory::class);
            expect($factory2)->toBeInstanceOf(ProcedureCallResultFactory::class);
        });

        test('boot method binds factory correctly', function () {
            ProcedureCallResultFactory::boot();
            
            $factory = app(ProcedureCallResultFactory::class);
            
            expect($factory)->toBeInstanceOf(ProcedureCallResultFactory::class);
        });
    });

    describe('edge cases and error handling', function () {
        
        test('handles container resolution failures gracefully', function () {
            $id = new RpcMessageID('resolution-test');
            
            // Mock container to throw exception
            $this->container->bind(ProcedureCallResultContract::class, function () {
                throw new \Exception('Resolution failed');
            });
            
            expect(fn() => $this->factory->result($id))
                ->toThrow(\Exception::class, 'Resolution failed');
        });

        test('creates different instances for different calls', function () {
            $id1 = new RpcMessageID('test-1');
            $id2 = new RpcMessageID('test-2');
            
            $result1 = \Mockery::mock(ProcedureCallResultContract::class);
            $result2 = \Mockery::mock(ProcedureCallResultContract::class);
            
            $callCount = 0;
            $this->container->bind(ProcedureCallResultContract::class, function () use (&$callCount, $result1, $result2) {
                $callCount++;
                return $callCount === 1 ? $result1 : $result2;
            });
            
            $factory1 = $this->factory->result($id1);
            $factory2 = $this->factory->result($id2);
            
            expect($factory1)->not->toBe($factory2);
        });
    });
});
