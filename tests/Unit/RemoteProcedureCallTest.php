<?php

declare(strict_types=1);

use ProjectSaturnStudios\RpcServer\RemoteProcedureCall;
use ProjectSaturnStudios\RpcServer\RpcRouter;
use Illuminate\Container\Container;

describe('RemoteProcedureCall Unit Tests', function () {
    
    describe('basic instantiation', function () {
        
        test('can create with method name only', function () {
            $procedure = new RemoteProcedureCall('test.method');
            
            expect($procedure)->toBeInstanceOf(RemoteProcedureCall::class);
            expect($procedure->getMethod())->toBe('test.method');
        });

        test('can create with action', function () {
            $action = function () { return 'test'; };
            $procedure = new RemoteProcedureCall('test.method', $action);
            
            expect($procedure->getMethod())->toBe('test.method');
            // Action gets wrapped in array format by RouteAction::parse
            expect($procedure->getAction())->toBeArray();
            expect($procedure->getAction('uses'))->toBe($action);
        });

        test('can create with container and router via setters', function () {
            $container = new Container();
            $router = new RpcRouter($container);
            $procedure = new RemoteProcedureCall('test.method');
            
            $procedure->setContainer($container);
            $procedure->setRouter($router);
            
            expect($procedure->getMethod())->toBe('test.method');
        });
    });

    describe('method manipulation', function () {
        
        test('can set method after instantiation', function () {
            $procedure = new RemoteProcedureCall('original.method');
            
            $result = $procedure->setMethod('new.method');
            
            expect($procedure->getMethod())->toBe('new.method');
            expect($result)->toBe($procedure); // Returns self for chaining
        });

        test('setMethod returns self for chaining', function () {
            $procedure = new RemoteProcedureCall('test.method');
            
            $result = $procedure->setMethod('new.method');
            
            expect($result)->toBe($procedure);
        });
    });

    describe('container and router setup', function () {
        
        test('can set container', function () {
            $procedure = new RemoteProcedureCall('test.method');
            $container = new Container();
            
            $result = $procedure->setContainer($container);
            
            expect($result)->toBe($procedure);
        });

        test('can set router', function () {
            $procedure = new RemoteProcedureCall('test.method');
            $router = new RpcRouter(new Container());
            
            $result = $procedure->setRouter($router);
            
            expect($result)->toBe($procedure);
        });
    });

    describe('prefix handling', function () {
        
        test('can set prefix modifies method name', function () {
            $procedure = new RemoteProcedureCall('test.method');
            
            $result = $procedure->prefix('api/v1');
            
            expect($result)->toBe($procedure);
            expect($procedure->getMethod())->toBe('api/v1/test.method');
        });

        test('handles null prefix correctly', function () {
            $procedure = new RemoteProcedureCall('test.method');
            
            $procedure->prefix(null);
            
            expect($procedure->getMethod())->toBe('test.method');
        });

        test('handles empty prefix correctly', function () {
            $procedure = new RemoteProcedureCall('test.method');
            
            $procedure->prefix('');
            
            expect($procedure->getMethod())->toBe('test.method');
        });

        test('normalizes slash prefixes', function () {
            $procedure = new RemoteProcedureCall('test.method');
            
            $procedure->prefix('/api/v1/');
            
            expect($procedure->getMethod())->toBe('api/v1/test.method');
        });

        test('prefix returns self for chaining', function () {
            $procedure = new RemoteProcedureCall('test.method');
            
            $result = $procedure->prefix('api/v1');
            
            expect($result)->toBe($procedure);
        });
    });

    describe('action manipulation', function () {
        
        test('can set action after instantiation', function () {
            $procedure = new RemoteProcedureCall('test.method');
            $action = ['uses' => function () { return 'test'; }];
            
            $result = $procedure->setAction($action);
            
            expect($procedure->getAction())->toBe($action);
            expect($result)->toBe($procedure);
        });

        test('can get specific action types', function () {
            $procedure = new RemoteProcedureCall('test.method');
            $action = ['uses' => 'Controller@method'];
            
            $procedure->setAction($action);
            
            expect($procedure->getAction())->toBe($action);
            expect($procedure->getAction('uses'))->toBe('Controller@method');
        });

        test('can get full action information', function () {
            $procedure = new RemoteProcedureCall('test.method');
            $action = ['uses' => 'Controller@method', 'middleware' => ['auth']];
            
            $procedure->setAction($action);
            
            expect($procedure->getAction())->toBe($action);
        });
    });

    describe('where constraints', function () {
        
        test('can add single where constraint', function () {
            $procedure = new RemoteProcedureCall('user.{id}');
            
            $result = $procedure->where('id', '[0-9]+');
            
            expect($result)->toBe($procedure);
        });

        test('can add multiple where constraints', function () {
            $procedure = new RemoteProcedureCall('user.{id}.posts.{post_id}');
            
            $result = $procedure->where(['id' => '[0-9]+', 'post_id' => '[0-9]+']);
            
            expect($result)->toBe($procedure);
        });

        test('where returns self for chaining', function () {
            $procedure = new RemoteProcedureCall('user.{id}');
            
            $result = $procedure->where('id', '[0-9]+');
            
            expect($result)->toBe($procedure);
        });
    });

    describe('parameter handling', function () {
        
        test('compiles parameters from method name via parameterNames', function () {
            $procedure = new RemoteProcedureCall('user.{id}.posts.{post_id}');
            
            $parameters = $procedure->parameterNames();
            
            expect($parameters)->toBeArray()
                ->and($parameters)->toContain('id')
                ->and($parameters)->toContain('post_id');
        });

        test('handles optional parameters', function () {
            $procedure = new RemoteProcedureCall('search.{query}.{page?}');
            
            $parameters = $procedure->parameterNames();
            
            expect($parameters)->toBeArray()
                ->and($parameters)->toContain('query')
                ->and($parameters)->toContain('page');
        });

        test('handles methods without parameters', function () {
            $procedure = new RemoteProcedureCall('simple.method');
            
            $parameters = $procedure->parameterNames();
            
            expect($parameters)->toBeArray()
                ->and($parameters)->toBeEmpty();
        });

        test('gets optional parameters correctly', function () {
            $procedure = new RemoteProcedureCall('search.{query}.{page?}');
            
            $optional = $procedure->getOptionalParameterNames();
            
            expect($optional)->toBeArray()
                ->and($optional)->toHaveKey('page')
                ->and($optional)->not->toHaveKey('query');
        });
    });

    describe('route compilation', function () {
        
        test('converts to symfony route', function () {
            $procedure = new RemoteProcedureCall('user.{id}');
            
            $route = $procedure->toSymfonyRoute();
            
            expect($route)->toBeInstanceOf(\Symfony\Component\Routing\Route::class);
        });

        test('handles route with constraints', function () {
            $procedure = new RemoteProcedureCall('user.{id}');
            $procedure->where('id', '[0-9]+');
            
            $route = $procedure->toSymfonyRoute();
            
            expect($route)->toBeInstanceOf(\Symfony\Component\Routing\Route::class);
        });
    });

    describe('validators', function () {
        
        test('has default validators array', function () {
            $procedure = new RemoteProcedureCall('test.method');
            
            $validators = $procedure->getValidators();
            
            expect($validators)->toBeArray();
        });

        test('validators are cached correctly', function () {
            $procedure = new RemoteProcedureCall('test.method');
            
            $validators1 = $procedure->getValidators();
            $validators2 = $procedure->getValidators();
            
            expect($validators1)->toBe($validators2);
        });
    });
});
