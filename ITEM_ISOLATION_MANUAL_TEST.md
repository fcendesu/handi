# Testing Item Isolation via Tinker

Let's test the item isolation implementation step by step using Laravel Tinker.

## Start Tinker Session
```bash
php artisan tinker
```

## Test Commands to Run in Tinker:

### 1. Clean up any existing test data
```php
DB::table('items')->where('item', 'like', 'Test Item%')->delete();
DB::table('users')->where('email', 'like', '%@test-isolation.com')->delete();
DB::table('companies')->where('email', 'like', '%@test-isolation.com')->delete();
```

### 2. Create Solo Handymen
```php
$solo1 = User::create([
    'name' => 'Solo Test 1',
    'email' => 'solo1@test-isolation.com', 
    'password' => bcrypt('password'),
    'user_type' => User::TYPE_SOLO_HANDYMAN,
]);

$solo2 = User::create([
    'name' => 'Solo Test 2',
    'email' => 'solo2@test-isolation.com',
    'password' => bcrypt('password'), 
    'user_type' => User::TYPE_SOLO_HANDYMAN,
]);
```

### 3. Create Companies and Admins
```php
$company1 = Company::create([
    'name' => 'Test Company 1',
    'address' => '123 Test St',
    'phone' => '555-0001',
    'email' => 'company1@test-isolation.com',
]);

$admin1 = User::create([
    'name' => 'Admin Test 1',
    'email' => 'admin1@test-isolation.com',
    'password' => bcrypt('password'),
    'user_type' => User::TYPE_COMPANY_ADMIN,
    'company_id' => $company1->id,
]);

$company1->update(['admin_id' => $admin1->id]);
```

### 4. Create Items for Each User
```php
// Solo handyman 1 creates an item
auth()->login($solo1);
$item1 = Item::create([
    'item' => 'Test Item Solo 1',
    'brand' => 'Brand A',
    'firm' => 'Firm A', 
    'price' => 100.00,
]);

// Solo handyman 2 creates an item  
auth()->login($solo2);
$item2 = Item::create([
    'item' => 'Test Item Solo 2',
    'brand' => 'Brand B',
    'firm' => 'Firm B',
    'price' => 200.00,
]);

// Company admin creates an item
auth()->login($admin1);
$item3 = Item::create([
    'item' => 'Test Item Company 1',
    'brand' => 'Brand C',
    'firm' => 'Firm C',
    'price' => 300.00,
]);
```

### 5. Test Isolation
```php
// Solo 1 should only see their item
auth()->login($solo1);
$solo1Items = Item::accessibleBy($solo1)->get();
echo "Solo 1 can see: " . $solo1Items->count() . " items\n";
echo "Items: " . $solo1Items->pluck('item')->implode(', ') . "\n";

// Solo 2 should only see their item  
auth()->login($solo2);
$solo2Items = Item::accessibleBy($solo2)->get();
echo "Solo 2 can see: " . $solo2Items->count() . " items\n";
echo "Items: " . $solo2Items->pluck('item')->implode(', ') . "\n";

// Company admin should only see their company's items
auth()->login($admin1);
$adminItems = Item::accessibleBy($admin1)->get();
echo "Admin 1 can see: " . $adminItems->count() . " items\n";
echo "Items: " . $adminItems->pluck('item')->implode(', ') . "\n";
```

### 6. Test Cross-Access Prevention
```php
// Solo 1 tries to see company items (should be 0)
auth()->login($solo1);
$crossAccessCount = Item::accessibleBy($solo1)->where('item', 'like', '%Company%')->count();
echo "Solo 1 can see company items: " . $crossAccessCount . "\n";

// Company admin tries to see solo items (should be 0)
auth()->login($admin1);  
$crossAccessCount2 = Item::accessibleBy($admin1)->where('item', 'like', '%Solo%')->count();
echo "Admin 1 can see solo items: " . $crossAccessCount2 . "\n";
```

### 7. Test Auto-Ownership
```php
// Check that items have correct ownership
echo "Item 1 user_id: " . $item1->user_id . " (should be " . $solo1->id . ")\n";
echo "Item 2 user_id: " . $item2->user_id . " (should be " . $solo2->id . ")\n";  
echo "Item 3 company_id: " . $item3->company_id . " (should be " . $company1->id . ")\n";
```

### 8. Cleanup
```php
DB::table('items')->where('item', 'like', 'Test Item%')->delete();
DB::table('users')->where('email', 'like', '%@test-isolation.com')->delete();
DB::table('companies')->where('email', 'like', '%@test-isolation.com')->delete();
```

## Expected Results

If item isolation is working correctly:

1. **Solo Handyman 1** should only see 1 item: "Test Item Solo 1"
2. **Solo Handyman 2** should only see 1 item: "Test Item Solo 2" 
3. **Company Admin 1** should only see 1 item: "Test Item Company 1"
4. **Cross-access counts** should all be 0
5. **Auto-ownership** should assign correct user_id/company_id values

This confirms that users can only see and access items they own, preventing data leakage between different users and organizations.
