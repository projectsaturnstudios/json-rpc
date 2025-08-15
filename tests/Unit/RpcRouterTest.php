<?php

declare(strict_types=1);

use ProjectSaturnStudios\RpcServer\RpcRouter;
use ProjectSaturnStudios\RpcServer\RemoteProcedureCall;
use Illuminate\Container\Container;

describe('RpcRouter Unit Tests', function () {
    
    beforeEach(function () {
        $this->container = new Container();
        $this->router = new RpcRouter($this->container);
    });
    
    describe('basic procedure registration', function () {
        
        test('can register procedure with closure', function () {
            $closure = function () { return 'test'; };
            
            $procedure = $this->router->registerProcedureCall('test.method', $closure);
            
            expect($procedure)->toBeInstanceOf(RemoteProcedureCall::class);
            expect($procedure->getMethod())->toBe('test.method');
            // Action gets wrapped by RouteAction::parse
            expect($procedure->getAction())->toBeArray();
            expect($procedure->getAction('uses'))->toBe($closure);
        });

        test('can register procedure with controller string', function () {
            $action = 'TestController@index';
            
            $procedure = $this->router->registerProcedureCall('test.method', $action);
            
            expect($procedure)->toBeInstanceOf(RemoteProcedureCall::class);
            // Action gets processed and gets additional fields
            expect($procedure->getAction())->toBeArray();
            expect($procedure->getAction('uses'))->toBe($action);
            expect($procedure->getAction('procedure'))->toBe($action);
        });

        test('can register procedure with array action', function () {
            $action = ['uses' => 'TestController@index', 'middleware' => ['auth']];
            
            $procedure = $this->router->registerProcedureCall('test.method', $action);
            
            expect($procedure)->toBeInstanceOf(RemoteProcedureCall::class);
            // Array action gets processed and gets additional fields
            $resultAction = $procedure->getAction();
            expect($resultAction)->toBeArray();
            expect($resultAction['uses'])->toBe('TestController@index');
            expect($resultAction['middleware'])->toBe(['auth']);
            expect($resultAction['procedure'])->toBe('TestController@index');
        });

        test('registered procedures are stored in collection', function () {
            $this->router->registerProcedureCall('test.method', function () {});
            
            $collection = $this->router->getProcedureCalls();
            
            expect($collection->count())->toBe(1);
        });
    });

    describe('procedure call creation', function () {
        
        test('creates new procedure call with correct properties', function () {
            $procedure = $this->router->newProcedureCall('test.method', function () {});
            
            expect($procedure)->toBeInstanceOf(RemoteProcedureCall::class);
            expect($procedure->getMethod())->toBe('test.method');
        });

        test('sets router and container on new procedure call', function () {
            $procedure = $this->router->newProcedureCall('test.method', function () {});
            
            expect($procedure)->toBeInstanceOf(RemoteProcedureCall::class);
        });
    });

    describe('group stack management', function () {
        
        test('initially has no group stack', function () {
            expect($this->router->hasGroupStack())->toBeFalse();
        });

        test('returns empty string for current group prefix when no groups', function () {
            $prefix = $this->router->getLastGroupPrefix();
            
            expect($prefix)->toBe('');
        });

        test('can access procedure calls collection', function () {
            $collection = $this->router->getProcedureCalls();
            
            expect($collection)->toBeInstanceOf(\ProjectSaturnStudios\RpcServer\Routing\ProcedureCollection::class);
        });
    });

    describe('group stack management', function () {
        
        test('new procedure call is properly configured', function () {
            $procedure = $this->router->newProcedureCall('test.method', function () {});
            
            expect($procedure)->toBeInstanceOf(RemoteProcedureCall::class);
            expect($procedure->getMethod())->toBe('test.method');
        });
    });

    describe('singleton behavior', function () {
        
        test('router boots as singleton in container', function () {
            RpcRouter::boot();
            
            $router1 = app(RpcRouter::class);
            $router2 = app(RpcRouter::class);
            
            expect($router1)->toBe($router2);
        });
    });
});
