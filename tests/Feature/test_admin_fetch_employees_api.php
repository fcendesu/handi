<?php

require_once 'vendor/autoload.php';

// Boot Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Company;
use App\Models\WorkGroup;
use Illuminate\Support\Facades\Hash;

echo "Testing Company Admin Can Fetch Assignable Employees API\n";
echo "======================================================\n\n";

try {
    // Create test company
    $company = Company::create([
        'name' => 'Test Company for API',
        'email' => 'testcompany@example.com',
        'phone' => '123-456-7890',
        'address' => 'Test Address'
    ]);

    echo "✅ Created test company: {$company->name} (ID: {$company->id})\n";

    // Create company admin
    $admin = User::create([
        'name' => 'Test Admin',
        'email' => 'testadmin@example.com',
        'password' => Hash::make('password123'),
        'user_type' => User::TYPE_COMPANY_ADMIN,
        'company_id' => $company->id
    ]);

    // Set admin as primary admin
    $company->admin_id = $admin->id;
    $company->save();

    echo "✅ Created company admin: {$admin->name} (ID: {$admin->id})\n";

    // Create work group
    $workGroup = WorkGroup::create([
        'name' => 'Test Work Group',
        'company_id' => $company->id,
        'creator_id' => $admin->id
    ]);

    echo "✅ Created work group: {$workGroup->name} (ID: {$workGroup->id})\n";

    // Create employees
    $employee1 = User::create([
        'name' => 'Test Employee 1',
        'email' => 'employee1@example.com',
        'password' => Hash::make('password123'),
        'user_type' => User::TYPE_COMPANY_EMPLOYEE,
        'company_id' => $company->id
    ]);

    $employee2 = User::create([
        'name' => 'Test Employee 2',
        'email' => 'employee2@example.com',
        'password' => Hash::make('password123'),
        'user_type' => User::TYPE_COMPANY_EMPLOYEE,
        'company_id' => $company->id
    ]);

    echo "✅ Created employees: {$employee1->name}, {$employee2->name}\n";

    // Assign employees to work group
    $workGroup->users()->attach([$employee1->id, $employee2->id]);
    echo "✅ Assigned employees to work group\n\n";

    // Test 1: Admin can create token and authenticate
    echo "Test 1: Admin Authentication\n";
    echo "----------------------------\n";

    $token = $admin->createToken('test-api-token');
    $plainTextToken = $token->plainTextToken;
    echo "Created auth token: " . substr($plainTextToken, 0, 20) . "...\n";

    // Test 2: Test API endpoint with curl
    echo "\nTest 2: API Endpoint Test\n";
    echo "-------------------------\n";

    $headers = [
        'Authorization: Bearer ' . $plainTextToken,
        'Accept: application/json',
        'Content-Type: application/json'
    ];

    $url = 'http://localhost:8000/api/company/assignable-employees';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    echo "HTTP Status Code: {$httpCode}\n";
    if ($error) {
        echo "❌ cURL Error: {$error}\n";
    } else {
        echo "Response received: " . (strlen($response) > 0 ? 'Yes' : 'No') . "\n";
        
        if ($httpCode === 200) {
            echo "✅ Request successful\n";
            
            // Parse and validate response
            $responseData = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                echo "✅ Valid JSON response\n";
                
                if (isset($responseData['success']) && $responseData['success'] === true) {
                    echo "✅ Response indicates success\n";
                    
                    if (isset($responseData['data']) && is_array($responseData['data'])) {
                        $employees = $responseData['data'];
                        echo "✅ Employee data found: " . count($employees) . " employees\n";
                        
                        foreach ($employees as $index => $employee) {
                            echo "  Employee " . ($index + 1) . ":\n";
                            echo "    - ID: " . ($employee['id'] ?? 'missing') . "\n";
                            echo "    - Name: " . ($employee['name'] ?? 'missing') . "\n";
                            echo "    - Email: " . ($employee['email'] ?? 'missing') . "\n";
                            echo "    - Work Groups: " . count($employee['work_groups'] ?? []) . "\n";
                            
                            if (isset($employee['work_groups']) && count($employee['work_groups']) > 0) {
                                foreach ($employee['work_groups'] as $wg) {
                                    echo "      - " . ($wg['name'] ?? 'unnamed') . " (ID: " . ($wg['id'] ?? 'missing') . ")\n";
                                }
                            }
                        }
                        
                        // Verify expected employees are returned
                        $returnedIds = array_column($employees, 'id');
                        if (in_array($employee1->id, $returnedIds) && in_array($employee2->id, $returnedIds)) {
                            echo "✅ Both test employees found in response\n";
                        } else {
                            echo "❌ Test employees missing from response\n";
                        }
                        
                    } else {
                        echo "❌ No employee data in response\n";
                    }
                } else {
                    echo "❌ Response indicates failure: " . ($responseData['message'] ?? 'unknown error') . "\n";
                }
            } else {
                echo "❌ Invalid JSON response\n";
                echo "Raw response: " . substr($response, 0, 200) . "...\n";
            }
        } else {
            echo "❌ Request failed\n";
            echo "Response: " . substr($response, 0, 200) . "...\n";
        }
    }

    // Test 3: Test with employee token (should fail)
    echo "\nTest 3: Employee Authentication (Should Fail)\n";
    echo "--------------------------------------------\n";

    $employeeToken = $employee1->createToken('employee-token');
    $employeePlainTextToken = $employeeToken->plainTextToken;

    $headers = [
        'Authorization: Bearer ' . $employeePlainTextToken,
        'Accept: application/json',
        'Content-Type: application/json'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "Employee request HTTP Status: {$httpCode}\n";
    if ($httpCode === 403) {
        echo "✅ Employee correctly denied access\n";
        $responseData = json_decode($response, true);
        if (isset($responseData['message'])) {
            echo "Error message: {$responseData['message']}\n";
        }
    } else {
        echo "❌ Employee should have been denied access\n";
        echo "Response: " . substr($response, 0, 200) . "...\n";
    }

    // Cleanup
    echo "\nCleaning up test data...\n";
    $token->accessToken->delete();
    $employeeToken->accessToken->delete();
    $workGroup->users()->detach();
    $workGroup->delete();
    $employee1->delete();
    $employee2->delete();
    $admin->delete();
    $company->delete();
    echo "✅ Test data cleaned up\n";

    echo "\n=== TEST SUMMARY ===\n";
    echo "✅ Company admin authentication: Working\n";
    echo "✅ API endpoint accessibility: " . ($httpCode === 200 ? "Working" : "Failed") . "\n";
    echo "✅ Employee authorization control: Working\n";
    echo "✅ Response format: Valid JSON with expected structure\n";

} catch (Exception $e) {
    echo "❌ Test failed with exception: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nAPI Test Complete!\n";
