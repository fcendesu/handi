<?php

use App\Models\Discovery;

describe('Discovery Model Priority Methods', function () {
    
    test('getPriorities returns array with correct structure', function () {
        $priorities = Discovery::getPriorities();
        
        expect($priorities)->toBeArray();
        expect($priorities)->toHaveCount(3);
        expect($priorities)->toHaveKeys([1, 2, 3]);
        expect(array_values($priorities))->toBe(['Low', 'Medium', 'High']);
    });
    
    test('getPriorityLabels returns array with correct structure', function () {
        $labels = Discovery::getPriorityLabels();
        
        expect($labels)->toBeArray();
        expect($labels)->toHaveCount(3);
        expect($labels)->toHaveKeys([1, 2, 3]);
        expect(array_values($labels))->toBe(['Low (Default)', 'Medium', 'High (Urgent)']);
    });
    
    test('priority constants have correct values', function () {
        expect(Discovery::PRIORITY_LOW)->toBe(1);
        expect(Discovery::PRIORITY_MEDIUM)->toBe(2);
        expect(Discovery::PRIORITY_HIGH)->toBe(3);
    });
    
    test('priority constants are integers', function () {
        expect(Discovery::PRIORITY_LOW)->toBeInt();
        expect(Discovery::PRIORITY_MEDIUM)->toBeInt();
        expect(Discovery::PRIORITY_HIGH)->toBeInt();
    });
    
    test('priority values are in ascending order', function () {
        expect(Discovery::PRIORITY_LOW)->toBeLessThan(Discovery::PRIORITY_MEDIUM);
        expect(Discovery::PRIORITY_MEDIUM)->toBeLessThan(Discovery::PRIORITY_HIGH);
    });
    
    test('all priority constants are represented in getPriorities', function () {
        $priorities = Discovery::getPriorities();
        
        expect($priorities)->toHaveKey(Discovery::PRIORITY_LOW);
        expect($priorities)->toHaveKey(Discovery::PRIORITY_MEDIUM);
        expect($priorities)->toHaveKey(Discovery::PRIORITY_HIGH);
    });
    
    test('all priority constants are represented in getPriorityLabels', function () {
        $labels = Discovery::getPriorityLabels();
        
        expect($labels)->toHaveKey(Discovery::PRIORITY_LOW);
        expect($labels)->toHaveKey(Discovery::PRIORITY_MEDIUM);
        expect($labels)->toHaveKey(Discovery::PRIORITY_HIGH);
    });
    
});
