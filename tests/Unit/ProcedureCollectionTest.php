<?php

declare(strict_types=1);

use ProjectSaturnStudios\RpcServer\Routing\ProcedureCollection;
use ProjectSaturnStudios\RpcServer\RemoteProcedureCall;

describe('ProcedureCollection Unit Tests', function () {
    
    describe('basic collection operations', function () {
        
        test('starts empty by default', function () {
            $collection = new ProcedureCollection();
            
            expect($collection)->toBeInstanceOf(ProcedureCollection::class);
            expect($collection->count())->toBe(0);
        });

        test('can add procedures to collection', function () {
            $collection = new ProcedureCollection();
            $procedure = new RemoteProcedureCall('test.method');
            
            $collection->add($procedure);
            
            expect($collection->count())->toBe(1);
        });

        test('added procedures are stored correctly', function () {
            $collection = new ProcedureCollection();
            $procedure = new RemoteProcedureCall('test.method');
            
            $collection->add($procedure);
            $procedures = $collection->getProcedureCalls();
            
            expect($procedures)->toBeArray()
                ->and($procedures)->toHaveCount(1)
                ->and($procedures[0])->toBe($procedure);
        });

        test('can get procedure calls array', function () {
            $collection = new ProcedureCollection();
            
            $procedures = $collection->getProcedureCalls();
            
            expect($procedures)->toBeArray()
                ->and($procedures)->toHaveCount(0);
        });

        test('returns procedure calls in order added', function () {
            $collection = new ProcedureCollection();
            $procedure1 = new RemoteProcedureCall('first.method');
            $procedure2 = new RemoteProcedureCall('second.method');
            
            $collection->add($procedure1);
            $collection->add($procedure2);
            
            $procedures = $collection->getProcedureCalls();
            expect($procedures[0])->toBe($procedure1)
                ->and($procedures[1])->toBe($procedure2);
        });
    });

    describe('procedure lookup and indexing', function () {
        
        test('add returns procedure for chaining', function () {
            $collection = new ProcedureCollection();
            $procedure = new RemoteProcedureCall('test.method');
            
            $result = $collection->add($procedure);
            
            expect($result)->toBe($procedure);
        });

        test('get method returns empty array for nonexistent method', function () {
            $collection = new ProcedureCollection();
            
            expect($collection->get('nonexistent'))->toBe([]);
        });
    });

    describe('request matching', function () {
        
        test('get method returns all procedures when no method specified', function () {
            $collection = new ProcedureCollection();
            $procedure = new RemoteProcedureCall('test.method');
            $collection->add($procedure);
            
            $all = $collection->get();
            
            expect($all)->toBeArray()
                ->and($all)->toHaveCount(1)
                ->and($all[0])->toBe($procedure);
        });
    });

    describe('multiple procedure handling', function () {
        
        test('can handle multiple procedures with different methods', function () {
            $collection = new ProcedureCollection();
            $procedure1 = new RemoteProcedureCall('user.create');
            $procedure2 = new RemoteProcedureCall('user.update');
            $procedure3 = new RemoteProcedureCall('post.create');
            
            $collection->add($procedure1);
            $collection->add($procedure2);
            $collection->add($procedure3);
            
            expect($collection->count())->toBe(3);
            
            $procedures = $collection->getProcedureCalls();
            expect($procedures)->toHaveCount(3)
                ->and($procedures[0])->toBe($procedure1)
                ->and($procedures[1])->toBe($procedure2)
                ->and($procedures[2])->toBe($procedure3);
        });

        test('later procedures override earlier ones with same method', function () {
            $collection = new ProcedureCollection();
            $procedure1 = new RemoteProcedureCall('same.method');
            $procedure2 = new RemoteProcedureCall('same.method');
            
            $collection->add($procedure1);
            $collection->add($procedure2);
            
            // The second procedure should override the first
            expect($collection->count())->toBe(1);
            $procedures = $collection->getProcedureCalls();
            expect($procedures[0])->toBe($procedure2);
        });
    });

    describe('collection state management', function () {
        
        test('maintains consistent count after multiple operations', function () {
            $collection = new ProcedureCollection();
            
            expect($collection->count())->toBe(0);
            
            $collection->add(new RemoteProcedureCall('method1'));
            expect($collection->count())->toBe(1);
            
            $collection->add(new RemoteProcedureCall('method2'));
            expect($collection->count())->toBe(2);
            
            $collection->add(new RemoteProcedureCall('method3'));
            expect($collection->count())->toBe(3);
        });
    });
});
