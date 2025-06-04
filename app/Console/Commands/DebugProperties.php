<?php

namespace App\Console\Commands;

use App\Models\Property;
use App\Models\User;
use Illuminate\Console\Command;

class DebugProperties extends Command
{
    protected $signature = 'debug:properties';
    protected $description = 'Debug property ownership and scoping';

    public function handle()
    {
        $this->info('=== Property Ownership Debug ===');

        // Check all properties
        $properties = Property::all(['id', 'name', 'company_id', 'user_id']);
        $this->info("Total properties: " . $properties->count());

        $this->info("\nProperties ownership data:");
        foreach ($properties as $prop) {
            $company_id = $prop->company_id ?? 'NULL';
            $user_id = $prop->user_id ?? 'NULL';
            $this->line("ID: {$prop->id}, Name: {$prop->name}, Company ID: {$company_id}, User ID: {$user_id}");
        }

        $this->info("\n=== User Types ===");
        $users = User::all(['id', 'name', 'email', 'user_type', 'company_id']);
        foreach ($users as $user) {
            $company_id = $user->company_id ?? 'NULL';
            $this->line("ID: {$user->id}, Name: {$user->name}, Type: {$user->user_type}, Company ID: {$company_id}");
        }

        $this->info("\n=== Testing Scoping ===");

        // Test scoping for each user type
        foreach ($users as $user) {
            $this->info("\nTesting user: {$user->name} (Type: {$user->user_type})");

            $accessibleProperties = Property::accessibleBy($user)->get(['id', 'name']);
            $this->info("Can access " . $accessibleProperties->count() . " properties:");

            foreach ($accessibleProperties as $prop) {
                $this->line("  - {$prop->name} (ID: {$prop->id})");
            }
        }

        return 0;
    }
}
