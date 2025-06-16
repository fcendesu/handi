<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use App\Http\Controllers\DiscoveryController;
use Illuminate\Support\Facades\Validator;

class TestDiscoveryValidation extends Command
{
    protected $signature = 'test:discovery-validation';
    protected $description = 'Test discovery manual address validation';

    public function handle()
    {
        $this->info('Testing Discovery Manual Address Validation...');

        // Test data for manual address
        $testData = [
            'customer_name' => 'John Doe',
            'customer_phone' => '1234567890',
            'customer_email' => 'john@example.com',
            'address_type' => 'manual',
            'manual_city' => 'Lefkoşa',
            'manual_district' => 'Dereboyu',
            'address_details' => 'Test Address 123',
            'discovery' => 'Test discovery content',
            'offer_valid_until' => '2025-12-31',
        ];

        // Create validation rules (same as in controller)
        $rules = [
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'address_type' => 'required|in:property,manual',
            'property_id' => 'nullable|exists:properties,id|required_if:address_type,property',
            'address' => 'nullable|string|max:1000',
            'manual_city' => 'nullable|string|max:255',
            'manual_district' => 'nullable|string|max:255',
            'address_details' => 'nullable|string|max:1000',
            'manual_latitude' => 'nullable|numeric|between:-90,90',
            'manual_longitude' => 'nullable|numeric|between:-180,180',
            'discovery' => 'required|string',
            'offer_valid_until' => 'required|date',
        ];

        $validator = Validator::make($testData, $rules);

        if ($validator->fails()) {
            $this->error('Validation failed:');
            foreach ($validator->errors()->all() as $error) {
                $this->line("  - $error");
            }
            return 1;
        }

        $this->info('✓ Basic validation passed');

        // Test address processing logic
        $addressParts = array_filter([
            $testData['manual_city'],
            $testData['manual_district'],
            $testData['address_details']
        ]);
        $finalAddress = implode(', ', $addressParts);

        $this->info("✓ Address would be: '$finalAddress'");

        // Test case 2: Only address details (no city/district)
        $testData2 = array_merge($testData, [
            'manual_city' => '',
            'manual_district' => '',
            'address_details' => 'Just a free-form address'
        ]);

        $validator2 = Validator::make($testData2, $rules);
        if ($validator2->fails()) {
            $this->error('Validation failed for address_details only:');
            foreach ($validator2->errors()->all() as $error) {
                $this->line("  - $error");
            }
        } else {
            $addressParts2 = array_filter([
                $testData2['manual_city'],
                $testData2['manual_district'],
                $testData2['address_details']
            ]);
            $finalAddress2 = implode(', ', $addressParts2);
            $this->info("✓ Address details only validation passed: '$finalAddress2'");
        }

        // Test case 3: Empty manual address
        $testData3 = array_merge($testData, [
            'manual_city' => '',
            'manual_district' => '',
            'address_details' => ''
        ]);

        $validator3 = Validator::make($testData3, $rules);
        if ($validator3->fails()) {
            $this->info('✓ Empty manual address correctly rejected');
        } else {
            $this->warn('Empty manual address should be rejected but passed validation');
        }

        $this->info('All tests completed!');
        return 0;
    }
}
