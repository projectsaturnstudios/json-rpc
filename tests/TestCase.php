<?php

namespace Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use ProjectSaturnStudios\RpcServer\Providers\RpcServerServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            RpcServerServiceProvider::class,
        ];
    }
    
    protected function getEnvironmentSetUp($app)
    {
        // Define environment setup
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        
        // Configure Laravel Data with proper normalizers to handle the PublishableConfig issue
        $app['config']->set('data', [
            'validation_strategy' => 'always',
            'max_transformation_depth' => 512,
            'throw_when_max_depth_reached' => true,
            'normalizers' => [
                \Spatie\LaravelData\Normalizers\ModelNormalizer::class,
                \Spatie\LaravelData\Normalizers\FormRequestNormalizer::class,
                \Spatie\LaravelData\Normalizers\ArrayableNormalizer::class,
                \Spatie\LaravelData\Normalizers\ObjectNormalizer::class,
                \Spatie\LaravelData\Normalizers\ArrayNormalizer::class,
                \Spatie\LaravelData\Normalizers\JsonNormalizer::class,
            ],
            'transformers' => [],
            'casts' => [],
            'rule_inferrers' => [],
            'value_transformers' => [],
        ]);
    }
}
