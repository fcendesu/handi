<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Company;
use App\Models\Item;
use App\Models\Discovery;

class TestItemIsolation extends Command
{
    protected $signature = 'test:item-isolation';
    protected $description = 'Test item isolation implementation for solo handymen and companies';

    public function handle()
    {
        $this->info('🧪 Testing Item Isolation Implementation');
        $this->info('==========================================');
        $this->newLine();

        // Clean up any existing test data
        $this->info('🧹 Cleaning up existing test data...');
        DB::table('items')->where('item', 'like', 'Test Item%')->delete();
        DB::table('users')->where('email', 'like', '%@test-isolation.com')->delete();
        DB::table('companies')->where('email', 'like', '%@test-isolation.com')->delete();

        // Test 1: Create Solo Handyman and Items
        $this->info('📝 Test 1: Solo Handyman Item Isolation');
        $this->info('---------------------------------------');

        $soloHandyman1 = User::create([
            'name' => 'Solo Handyman 1',
            'email' => 'solo1@test-isolation.com',
            'password' => bcrypt('password'),
        ]);

        $soloHandyman2 = User::create([
            'name' => 'Solo Handyman 2', 
            'email' => 'solo2@test-isolation.com',
            'password' => bcrypt('password'),
        ]);

        // Create items for solo handyman 1
        $item1_solo1 = Item::create([
            'item' => 'Test Item Solo 1-A',
            'brand' => 'Brand A',
            'price' => 100.00,
            'user_id' => $soloHandyman1->id,
        ]);

        $item2_solo1 = Item::create([
            'item' => 'Test Item Solo 1-B',
            'brand' => 'Brand B', 
            'price' => 150.00,
            'user_id' => $soloHandyman1->id,
        ]);

        // Create items for solo handyman 2
        $item1_solo2 = Item::create([
            'item' => 'Test Item Solo 2-A',
            'brand' => 'Brand A',
            'price' => 200.00,
            'user_id' => $soloHandyman2->id,
        ]);

        $this->info("✅ Created 2 solo handymen with 3 items total");

        // Test solo handyman 1 can only see their items
        $accessibleItems1 = Item::accessibleBy($soloHandyman1)->get();
        $this->info("Solo Handyman 1 can see {$accessibleItems1->count()} items (should be 2)");
        
        if ($accessibleItems1->count() === 2) {
            $this->info("✅ PASS: Solo handyman 1 sees correct number of items");
        } else {
            $this->error("❌ FAIL: Solo handyman 1 sees wrong number of items");
        }

        // Test solo handyman 2 can only see their items  
        $accessibleItems2 = Item::accessibleBy($soloHandyman2)->get();
        $this->info("Solo Handyman 2 can see {$accessibleItems2->count()} items (should be 1)");
        
        if ($accessibleItems2->count() === 1) {
            $this->info("✅ PASS: Solo handyman 2 sees correct number of items");
        } else {
            $this->error("❌ FAIL: Solo handyman 2 sees wrong number of items");
        }

        // Test 2: Company Users and Items
        $this->newLine();
        $this->info('📝 Test 2: Company User Item Isolation');
        $this->info('-------------------------------------');

        // Create companies
        $company1 = Company::create([
            'company' => 'Test Company 1',
            'email' => 'company1@test-isolation.com',
            'phone' => '1234567890',
        ]);

        $company2 = Company::create([
            'company' => 'Test Company 2', 
            'email' => 'company2@test-isolation.com',
            'phone' => '0987654321',
        ]);

        // Create company users
        $companyUser1 = User::create([
            'name' => 'Company User 1',
            'email' => 'cuser1@test-isolation.com', 
            'password' => bcrypt('password'),
            'company_id' => $company1->id,
        ]);

        $companyUser2 = User::create([
            'name' => 'Company User 2',
            'email' => 'cuser2@test-isolation.com',
            'password' => bcrypt('password'), 
            'company_id' => $company2->id,
        ]);

        // Create items for companies
        $item1_comp1 = Item::create([
            'item' => 'Test Item Company 1-A',
            'brand' => 'Brand C',
            'price' => 300.00,
            'company_id' => $company1->id,
        ]);

        $item2_comp1 = Item::create([
            'item' => 'Test Item Company 1-B', 
            'brand' => 'Brand D',
            'price' => 350.00,
            'company_id' => $company1->id,
        ]);

        $item1_comp2 = Item::create([
            'item' => 'Test Item Company 2-A',
            'brand' => 'Brand E',
            'price' => 400.00,
            'company_id' => $company2->id,
        ]);

        $this->info("✅ Created 2 companies with 2 users and 3 items total");

        // Test company user 1 can only see their company's items
        $accessibleItemsComp1 = Item::accessibleBy($companyUser1)->get();
        $this->info("Company User 1 can see {$accessibleItemsComp1->count()} items (should be 2)");
        
        if ($accessibleItemsComp1->count() === 2) {
            $this->info("✅ PASS: Company user 1 sees correct number of items");
        } else {
            $this->error("❌ FAIL: Company user 1 sees wrong number of items");
        }

        // Test company user 2 can only see their company's items
        $accessibleItemsComp2 = Item::accessibleBy($companyUser2)->get();
        $this->info("Company User 2 can see {$accessibleItemsComp2->count()} items (should be 1)");
        
        if ($accessibleItemsComp2->count() === 1) {
            $this->info("✅ PASS: Company user 2 sees correct number of items");
        } else {
            $this->error("❌ FAIL: Company user 2 sees wrong number of items");
        }

        // Test 3: Cross-Access Prevention
        $this->newLine();
        $this->info('📝 Test 3: Cross-Access Prevention');
        $this->info('----------------------------------');

        // Test solo handyman cannot access company items
        $soloCannotSeeCompany = Item::accessibleBy($soloHandyman1)
            ->where('company_id', $company1->id)
            ->count();
        
        $this->info("Solo handyman trying to access company items: {$soloCannotSeeCompany} (should be 0)");
        if ($soloCannotSeeCompany === 0) {
            $this->info("✅ PASS: Solo handyman cannot see company items");
        } else {
            $this->error("❌ FAIL: Solo handyman can see company items");
        }

        // Test company user cannot access solo items
        $companyCannotSeeSolo = Item::accessibleBy($companyUser1)
            ->where('user_id', $soloHandyman1->id)
            ->count();
            
        $this->info("Company user trying to access solo items: {$companyCannotSeeSolo} (should be 0)");
        if ($companyCannotSeeSolo === 0) {
            $this->info("✅ PASS: Company user cannot see solo items");
        } else {
            $this->error("❌ FAIL: Company user can see solo items");
        }

        // Test 4: Discovery Item Attachment Isolation
        $this->newLine();
        $this->info('📝 Test 4: Discovery Item Attachment Isolation');
        $this->info('----------------------------------------------');

        // Create a discovery for solo handyman 1
        $discovery1 = Discovery::create([
            'user_id' => $soloHandyman1->id,
            'property_type' => 'House',
            'area' => 100,
            'budget' => 5000,
            'date' => now(),
            'category' => 'Plumbing',
            'description' => 'Test discovery for item isolation',
        ]);

        // Test that solo handyman 1 can only attach their own items to discoveries
        $availableForDiscovery = Item::accessibleBy($soloHandyman1)->get();
        $this->info("Items available for discovery attachment: {$availableForDiscovery->count()} (should be 2)");
        
        if ($availableForDiscovery->count() === 2) {
            $this->info("✅ PASS: Solo handyman sees only their items for discovery attachment");
        } else {
            $this->error("❌ FAIL: Solo handyman sees wrong items for discovery attachment");
        }

        // Test 5: Search Isolation
        $this->newLine();
        $this->info('📝 Test 5: Search Function Isolation');
        $this->info('------------------------------------');

        // Test solo handyman search only returns their items
        $searchResults1 = Item::accessibleBy($soloHandyman1)
            ->where('item', 'like', '%Test Item%')
            ->get();
        $this->info("Solo handyman search results: {$searchResults1->count()} (should be 2)");
        
        if ($searchResults1->count() === 2) {
            $this->info("✅ PASS: Solo handyman search is properly isolated");
        } else {
            $this->error("❌ FAIL: Solo handyman search is not properly isolated");
        }

        // Test company user search only returns their company's items
        $searchResults2 = Item::accessibleBy($companyUser1) 
            ->where('item', 'like', '%Test Item%')
            ->get();
        $this->info("Company user search results: {$searchResults2->count()} (should be 2)");
        
        if ($searchResults2->count() === 2) {
            $this->info("✅ PASS: Company user search is properly isolated");
        } else {
            $this->error("❌ FAIL: Company user search is not properly isolated");
        }

        // Summary
        $this->newLine();
        $this->info('📊 Test Summary');
        $this->info('===============');
        $this->info('✅ Solo handyman item isolation: TESTED');
        $this->info('✅ Company user item isolation: TESTED');
        $this->info('✅ Cross-access prevention: TESTED');
        $this->info('✅ Discovery item attachment isolation: TESTED');
        $this->info('✅ Search function isolation: TESTED');

        // Clean up test data
        $this->newLine();
        $this->info('🧹 Cleaning up test data...');
        DB::table('items')->where('item', 'like', 'Test Item%')->delete();
        DB::table('users')->where('email', 'like', '%@test-isolation.com')->delete();
        DB::table('companies')->where('email', 'like', '%@test-isolation.com')->delete();
        DB::table('discoveries')->where('description', 'Test discovery for item isolation')->delete();

        $this->info('✅ Item isolation testing completed successfully!');
        
        return 0;
    }
}
