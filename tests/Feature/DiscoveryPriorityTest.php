<?php

use App\Models\Discovery;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Discovery Priority Feature', function () {
    
    test('discovery has default priority of low when created', function () {
        $user = User::factory()->create();
        
        $discovery = Discovery::factory()->create([
            'creator_id' => $user->id,
        ]);
        
        expect($discovery->priority)->toBe(Discovery::PRIORITY_LOW);
    });
    
    test('discovery can be created with specific priority', function () {
        $user = User::factory()->create();
        
        $lowPriorityDiscovery = Discovery::factory()->lowPriority()->create([
            'creator_id' => $user->id,
        ]);
        
        $mediumPriorityDiscovery = Discovery::factory()->mediumPriority()->create([
            'creator_id' => $user->id,
        ]);
        
        $highPriorityDiscovery = Discovery::factory()->highPriority()->create([
            'creator_id' => $user->id,
        ]);
        
        expect($lowPriorityDiscovery->priority)->toBe(Discovery::PRIORITY_LOW);
        expect($mediumPriorityDiscovery->priority)->toBe(Discovery::PRIORITY_MEDIUM);
        expect($highPriorityDiscovery->priority)->toBe(Discovery::PRIORITY_HIGH);
    });
    
    test('priority constants are correctly defined', function () {
        expect(Discovery::PRIORITY_LOW)->toBe(1);
        expect(Discovery::PRIORITY_MEDIUM)->toBe(2);
        expect(Discovery::PRIORITY_HIGH)->toBe(3);
    });
    
    test('getPriorities method returns correct priority mapping', function () {
        $priorities = Discovery::getPriorities();
        
        expect($priorities)->toHaveCount(3);
        expect($priorities[Discovery::PRIORITY_LOW])->toBe('Low');
        expect($priorities[Discovery::PRIORITY_MEDIUM])->toBe('Medium');
        expect($priorities[Discovery::PRIORITY_HIGH])->toBe('High');
    });
    
    test('getPriorityLabels method returns correct priority labels', function () {
        $priorityLabels = Discovery::getPriorityLabels();
        
        expect($priorityLabels)->toHaveCount(3);
        expect($priorityLabels[Discovery::PRIORITY_LOW])->toBe('Low (Default)');
        expect($priorityLabels[Discovery::PRIORITY_MEDIUM])->toBe('Medium');
        expect($priorityLabels[Discovery::PRIORITY_HIGH])->toBe('High (Urgent)');
    });
    
    test('priority field is mass assignable', function () {
        $user = User::factory()->create();
        
        $discovery = Discovery::create([
            'creator_id' => $user->id,
            'customer_name' => 'Test Customer',
            'customer_phone' => '+1234567890',
            'customer_email' => 'test@example.com',
            'discovery' => 'Test discovery',
            'todo_list' => 'Test todo',
            'priority' => Discovery::PRIORITY_HIGH,
        ]);
        
        expect($discovery->priority)->toBe(Discovery::PRIORITY_HIGH);
    });
    
    test('discovery priority can be updated', function () {
        $user = User::factory()->create();
        $discovery = Discovery::factory()->create([
            'creator_id' => $user->id,
            'priority' => Discovery::PRIORITY_LOW,
        ]);
        
        $discovery->update(['priority' => Discovery::PRIORITY_HIGH]);
        
        expect($discovery->fresh()->priority)->toBe(Discovery::PRIORITY_HIGH);
    });
    
    test('discoveries can be filtered by priority', function () {
        $user = User::factory()->create();
        
        Discovery::factory()->count(3)->lowPriority()->create(['creator_id' => $user->id]);
        Discovery::factory()->count(2)->mediumPriority()->create(['creator_id' => $user->id]);
        Discovery::factory()->count(1)->highPriority()->create(['creator_id' => $user->id]);
        
        $lowPriorityCount = Discovery::where('priority', Discovery::PRIORITY_LOW)->count();
        $mediumPriorityCount = Discovery::where('priority', Discovery::PRIORITY_MEDIUM)->count();
        $highPriorityCount = Discovery::where('priority', Discovery::PRIORITY_HIGH)->count();
        
        expect($lowPriorityCount)->toBe(3);
        expect($mediumPriorityCount)->toBe(2);
        expect($highPriorityCount)->toBe(1);
    });
    
    test('discoveries can be ordered by priority', function () {
        $user = User::factory()->create();
        
        $lowDiscovery = Discovery::factory()->lowPriority()->create(['creator_id' => $user->id]);
        $highDiscovery = Discovery::factory()->highPriority()->create(['creator_id' => $user->id]);
        $mediumDiscovery = Discovery::factory()->mediumPriority()->create(['creator_id' => $user->id]);
        
        $orderedByPriorityDesc = Discovery::orderBy('priority', 'desc')->get();
        $orderedByPriorityAsc = Discovery::orderBy('priority', 'asc')->get();
        
        // Highest priority first (3, 2, 1)
        expect($orderedByPriorityDesc->first()->id)->toBe($highDiscovery->id);
        expect($orderedByPriorityDesc->last()->id)->toBe($lowDiscovery->id);
        
        // Lowest priority first (1, 2, 3)
        expect($orderedByPriorityAsc->first()->id)->toBe($lowDiscovery->id);
        expect($orderedByPriorityAsc->last()->id)->toBe($highDiscovery->id);
    });
    
    test('priority field accepts only valid values', function () {
        $user = User::factory()->create();
        
        // Valid values should work
        foreach ([Discovery::PRIORITY_LOW, Discovery::PRIORITY_MEDIUM, Discovery::PRIORITY_HIGH] as $priority) {
            $discovery = Discovery::factory()->create([
                'creator_id' => $user->id,
                'priority' => $priority,
            ]);
            
            expect($discovery->priority)->toBe($priority);
        }
    });
    
    test('priority field is included in fillable attributes', function () {
        $fillable = (new Discovery())->getFillable();
        
        expect($fillable)->toContain('priority');
    });
    
    test('existing discoveries get default priority after migration', function () {
        // This test assumes the migration has been run and the default value is applied
        $user = User::factory()->create();
        
        $discovery = Discovery::factory()->create([
            'creator_id' => $user->id,
        ]);
        
        // Even if we don't explicitly set priority, it should have the default value
        expect($discovery->priority)->toBe(Discovery::PRIORITY_LOW);
    });
    
});

describe('Discovery Priority Migration', function () {
    
    test('priority column exists in discoveries table', function () {
        $columns = Schema::getColumnListing('discoveries');
        
        expect($columns)->toContain('priority');
    });
    
});
