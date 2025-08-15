<?php

declare(strict_types=1);

use ProjectSaturnStudios\RpcServer\RpcRouter;
use ProjectSaturnStudios\RpcServer\RemoteProcedureCall;

describe('RpcRouter Integration Tests', function () {
    
    beforeEach(function () {
        // Create a fresh router instance for each test to avoid state bleeding
        $this->router = new \ProjectSaturnStudios\RpcServer\RpcRouter(app());
    });
    
    describe('end-to-end procedure registration and execution', function () {
        
        test('can register and retrieve simple closure procedure', function () {
            $called = false;
            $procedure = $this->router->registerProcedureCall('test.closure', function () use (&$called) {
                $called = true;
                return 'closure result';
            });
            
            expect($procedure)->toBeInstanceOf(RemoteProcedureCall::class);
            expect($procedure->getMethod())->toBe('test.closure');
            
            $collection = $this->router->getProcedureCalls();
            expect($collection->count())->toBe(1);
        });

        test('can register controller action procedure', function () {
            $procedure = $this->router->registerProcedureCall('test.controller', 'TestController@index');
            
            expect($procedure->getAction())->toBeArray();
            expect($procedure->getAction('uses'))->toBe('TestController@index');
        });

        test('can register procedure with middleware and constraints', function () {
            $action = [
                'uses' => 'UserController@show',
                'middleware' => ['auth', 'verified'],
            ];
            
            $procedure = $this->router->registerProcedureCall('user.{id}', $action);
            $procedure->where('id', '[0-9]+');
            
            $resultAction = $procedure->getAction();
            expect($resultAction['uses'])->toBe('UserController@show');
            expect($resultAction['middleware'])->toBe(['auth', 'verified']);
            expect($procedure->getMethod())->toBe('user.{id}');
        });

        test('procedures are stored and retrievable from collection', function () {
            $this->router->registerProcedureCall('procedure.one', function () {});
            $this->router->registerProcedureCall('procedure.two', function () {});
            $this->router->registerProcedureCall('procedure.three', function () {});
            
            $collection = $this->router->getProcedureCalls();
            
            expect($collection->count())->toBe(3);
            
            $procedures = $collection->getProcedureCalls();
            expect($procedures)->toHaveCount(3);
            expect($procedures[0]->getMethod())->toBe('procedure.one');
            expect($procedures[1]->getMethod())->toBe('procedure.two');
            expect($procedures[2]->getMethod())->toBe('procedure.three');
        });
    });

    describe('procedure replacement behavior', function () {
        
        test('later procedures can override earlier ones', function () {
            $firstAction = function () { return 'first'; };
            $secondAction = function () { return 'second'; };
            
            $procedure1 = $this->router->registerProcedureCall('same.method', $firstAction);
            $procedure2 = $this->router->registerProcedureCall('same.method', $secondAction);
            
            expect($procedure1->getAction('uses'))->toBe($firstAction);
            expect($procedure2->getAction('uses'))->toBe($secondAction);
            
            // The second registration should override the first (replacement behavior)
            $collection = $this->router->getProcedureCalls();
            expect($collection->count())->toBe(1);
            
            // The collection should contain the latest procedure
            $procedures = $collection->getProcedureCalls();
            expect($procedures[0]->getAction('uses'))->toBe($secondAction);
        });
    });

    describe('container integration', function () {
        
        test('procedures are created with container reference', function () {
            $procedure = $this->router->registerProcedureCall('test.method', function () {});
            
            expect($procedure)->toBeInstanceOf(RemoteProcedureCall::class);
        });

        test('router can be resolved from container', function () {
            $router = app(RpcRouter::class);
            
            expect($router)->toBeInstanceOf(RpcRouter::class);
        });
    });

    describe('complex routing scenarios', function () {
        
        test('can handle nested parameter methods', function () {
            $procedure = $this->router->registerProcedureCall('user.{id}.posts.{post_id}', function ($id, $post_id) {
                return ['user_id' => $id, 'post_id' => $post_id];
            });
            
            $parameters = $procedure->parameterNames();
            
            expect($parameters)->toContain('id')
                ->and($parameters)->toContain('post_id');
        });

        test('can handle optional parameters', function () {
            $procedure = $this->router->registerProcedureCall('search.{query}.{page?}', function ($query, $page = 1) {
                return ['query' => $query, 'page' => $page];
            });
            
            $parameters = $procedure->parameterNames();
            $optional = $procedure->getOptionalParameterNames();
            
            expect($parameters)->toContain('query')
                ->and($parameters)->toContain('page')
                ->and($optional)->toHaveKey('page')
                ->and($optional)->not->toHaveKey('query');
        });

        test('can handle multiple constraints on parameters', function () {
            $procedure = $this->router->registerProcedureCall('user.{id}.posts.{post_id}', function () {});
            $procedure->where(['id' => '[0-9]+', 'post_id' => '[0-9]+']);
            
            expect($procedure)->toBeInstanceOf(RemoteProcedureCall::class);
        });
    });

    describe('real-world usage patterns', function () {
        
        test('can register CRUD operations for a resource', function () {
            $this->router->registerProcedureCall('users.index', 'UserController@index');
            $this->router->registerProcedureCall('users.{id}.show', 'UserController@show');
            $this->router->registerProcedureCall('users.create', 'UserController@create');
            $this->router->registerProcedureCall('users.{id}.update', 'UserController@update');
            $this->router->registerProcedureCall('users.{id}.delete', 'UserController@delete');
            
            $collection = $this->router->getProcedureCalls();
            expect($collection->count())->toBe(5);
        });

        test('can register API versioned procedures', function () {
            $this->router->registerProcedureCall('api.v1.users.index', 'V1\\UserController@index');
            $this->router->registerProcedureCall('api.v2.users.index', 'V2\\UserController@index');
            
            $collection = $this->router->getProcedureCalls();
            expect($collection->count())->toBe(2);
            
            $procedures = $collection->getProcedureCalls();
            expect($procedures[0]->getMethod())->toBe('api.v1.users.index');
            expect($procedures[1]->getMethod())->toBe('api.v2.users.index');
        });
    });

    describe('error cases and edge conditions', function () {
        
        test('handles empty method name gracefully', function () {
            $procedure = $this->router->registerProcedureCall('', function () {});
            
            // Empty method gets normalized to '/' by prefix logic
            expect($procedure->getMethod())->toBe('/');
        });

        test('handles null action gracefully', function () {
            $procedure = $this->router->registerProcedureCall('test.method', null);
            
            // Null action gets processed by RouteAction::parse
            expect($procedure->getAction())->toBeArray();
        });

        test('handles complex nested parameter patterns', function () {
            $procedure = $this->router->registerProcedureCall('api.{version}.users.{user_id}.posts.{post_id}.comments.{comment_id?}', function () {});
            
            $parameters = $procedure->parameterNames();
            
            expect($parameters)->toContain('version')
                ->and($parameters)->toContain('user_id')
                ->and($parameters)->toContain('post_id')
                ->and($parameters)->toContain('comment_id');
        });
    });
});
