<?php

declare(strict_types=1);

use ProjectSaturnStudios\RpcServer\RpcRouter;
use ProjectSaturnStudios\RpcServer\Console\Commands\ListProceduresCommand;
use ProjectSaturnStudios\RpcServer\Console\Commands\MakeProcedureControllerCommand;
use Illuminate\Support\Facades\File;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Tester\CommandTester;

describe('Basic Console Commands Tests', function () {
    
    describe('ListProceduresCommand', function () {
        
        test('command instantiates correctly', function () {
            $command = new ListProceduresCommand();
            
            expect($command)->toBeInstanceOf(ListProceduresCommand::class);
            expect($command->getName())->toBe('procedure:list');
        });
        
        test('shows empty message when no procedures registered', function () {
            // Create fresh router
            $router = new RpcRouter(app());
            app()->instance(RpcRouter::class, $router);
            
            $command = new ListProceduresCommand();
            $command->setLaravel(app());
            
            $commandTester = new CommandTester($command);
            $commandTester->execute([]);
            
            expect($commandTester->getStatusCode())->toBe(0);
            expect($commandTester->getDisplay())->toContain("doesn't have any RPC procedures");
        });
        
        test('shows procedures when they exist', function () {
            // Create router with procedures
            $router = new RpcRouter(app());
            $router->registerProcedureCall('test.hello', function () {
                return 'Hello World';
            });
            app()->instance(RpcRouter::class, $router);
            
            $command = new ListProceduresCommand();
            $command->setLaravel(app());
            
            $commandTester = new CommandTester($command);
            $commandTester->execute([]);
            
            expect($commandTester->getStatusCode())->toBe(0);
            $output = $commandTester->getDisplay();
            expect($output)->toContain('test.hello');
        });
        
        test('can output JSON format', function () {
            // Create router with procedures
            $router = new RpcRouter(app());
            $router->registerProcedureCall('test.hello', function () {
                return 'Hello World';
            });
            app()->instance(RpcRouter::class, $router);
            
            $command = new ListProceduresCommand();
            $command->setLaravel(app());
            
            $commandTester = new CommandTester($command);
            $commandTester->execute(['--json' => true]);
            
            expect($commandTester->getStatusCode())->toBe(0);
            $output = $commandTester->getDisplay();
            
            // Should be valid JSON
            $decoded = json_decode($output, true);
            expect($decoded)->not->toBeNull();
            expect($decoded)->toBeArray();
        });
    });
    
    describe('MakeProcedureControllerCommand', function () {
        
        test('command instantiates correctly', function () {
            $command = new MakeProcedureControllerCommand(new Filesystem());
            
            expect($command)->toBeInstanceOf(MakeProcedureControllerCommand::class);
            expect($command->getName())->toBe('make:procedure');
        });
        
        test('command extends GeneratorCommand', function () {
            $command = new MakeProcedureControllerCommand(new Filesystem());
            
            expect($command)->toBeInstanceOf(\Illuminate\Console\GeneratorCommand::class);
        });
        
        test('command has correct signature', function () {
            $command = new MakeProcedureControllerCommand(new Filesystem());
            $command->setLaravel(app());
            
            expect($command->getName())->toBe('make:procedure');
            expect($command->getDescription())->toContain('procedure');
        });
    });
});
