<?php

declare(strict_types=1);

describe('Basic Pest Testing Environment', function () {
    
    test('basic test works', function () {
        expect(true)->toBeTrue();
    });

    test('math works', function () {
        expect(2 + 2)->toBe(4);
    });

    test('strings work', function () {
        expect('hello')->toBe('hello');
    });

    test('arrays work', function () {
        expect([1, 2, 3])->toContain(2);
    });

    test('custom expectation works', function () {
        expect(5)->toBeGreaterThan(3);
    });

});

describe('Pest Testing Environment', function () {
    
    test('can use expectations', function () {
        expect(collect([1, 2, 3]))->toHaveCount(3);
    });

    test('can test objects', function () {
        $obj = new stdClass();
        $obj->name = 'test';
        
        expect($obj)->toHaveProperty('name');
        expect($obj->name)->toBe('test');
    });

});
