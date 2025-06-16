<?php

use App\Models\User;
use App\Models\Company;
use App\Services\DiscoveryImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DiscoveryImageServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_stores_image_for_solo_handyman_with_organized_structure()
    {
        $user = User::factory()->create([
            'user_type' => User::TYPE_SOLO_HANDYMAN,
            'company_id' => null,
        ]);

        $image = UploadedFile::fake()->image('test-image.jpg', 800, 600);

        $storedPath = DiscoveryImageService::storeDiscoveryImage($image, $user);

        // Check that path follows expected structure
        $expectedPathPattern = "discoveries/solo-handyman-{$user->id}/" . strtolower(now()->format('F-Y')) . "/";
        $this->assertStringStartsWith($expectedPathPattern, $storedPath);
        $this->assertStringEndsWith('.jpg', $storedPath);

        // Verify file exists
        Storage::disk('public')->assertExists($storedPath);
    }

    public function test_stores_image_for_company_user_with_organized_structure()
    {
        $company = Company::factory()->create(['name' => 'Test Company LLC']);
        $user = User::factory()->create([
            'user_type' => User::TYPE_COMPANY_ADMIN,
            'company_id' => $company->id,
        ]);

        $image = UploadedFile::fake()->image('company-image.png', 1024, 768);

        $storedPath = DiscoveryImageService::storeDiscoveryImage($image, $user);

        // Check that path follows expected structure
        $expectedPathPattern = "discoveries/test-company-llc-{$company->id}/" . strtolower(now()->format('F-Y')) . "/";
        $this->assertStringStartsWith($expectedPathPattern, $storedPath);
        $this->assertStringEndsWith('.png', $storedPath);

        Storage::disk('public')->assertExists($storedPath);
    }

    public function test_deletes_discovery_image()
    {
        $user = User::factory()->create([
            'user_type' => User::TYPE_SOLO_HANDYMAN,
        ]);

        $image = UploadedFile::fake()->image('delete-test.jpg');
        $storedPath = DiscoveryImageService::storeDiscoveryImage($image, $user);

        // Verify file exists
        Storage::disk('public')->assertExists($storedPath);

        // Delete the image
        $result = DiscoveryImageService::deleteDiscoveryImage($storedPath);

        $this->assertTrue($result);
        Storage::disk('public')->assertMissing($storedPath);
    }

    public function test_sanitizes_company_name_for_folder()
    {
        $company = Company::factory()->create(['name' => 'A Company With Spaces & Special!@# Characters']);
        $user = User::factory()->create([
            'user_type' => User::TYPE_COMPANY_ADMIN,
            'company_id' => $company->id,
        ]);

        $image = UploadedFile::fake()->image('sanitize-test.jpg');
        $storedPath = DiscoveryImageService::storeDiscoveryImage($image, $user);

        // Should contain sanitized company name
        $expectedPathPattern = "discoveries/a-company-with-spaces-special-characters-{$company->id}/";
        $this->assertStringStartsWith($expectedPathPattern, $storedPath);
    }

    public function test_generates_unique_filenames()
    {
        $user = User::factory()->create([
            'user_type' => User::TYPE_SOLO_HANDYMAN,
        ]);

        $image1 = UploadedFile::fake()->image('same-name.jpg');
        $image2 = UploadedFile::fake()->image('same-name.jpg');

        $path1 = DiscoveryImageService::storeDiscoveryImage($image1, $user);
        $path2 = DiscoveryImageService::storeDiscoveryImage($image2, $user);

        $this->assertNotEquals($path1, $path2);

        // Both files should exist
        Storage::disk('public')->assertExists($path1);
        Storage::disk('public')->assertExists($path2);
    }

    public function test_migrates_existing_images()
    {
        $user = User::factory()->create([
            'user_type' => User::TYPE_SOLO_HANDYMAN,
        ]);

        // Create some "old" images in the old structure
        $oldImage1 = 'discoveries/old-image-1.jpg';
        $oldImage2 = 'discoveries/old-image-2.png';

        Storage::disk('public')->put($oldImage1, 'fake image content 1');
        Storage::disk('public')->put($oldImage2, 'fake image content 2');

        $oldPaths = [$oldImage1, $oldImage2];

        $newPaths = DiscoveryImageService::migrateExistingImages($oldPaths, $user);

        $this->assertCount(2, $newPaths);

        // Check new paths follow organized structure
        foreach ($newPaths as $newPath) {
            $expectedPathPattern = "discoveries/solo-handyman-{$user->id}/" . strtolower(now()->format('F-Y')) . "/";
            $this->assertStringStartsWith($expectedPathPattern, $newPath);
            Storage::disk('public')->assertExists($newPath);
        }

        // Old files should be moved (no longer exist in old location)
        Storage::disk('public')->assertMissing($oldImage1);
        Storage::disk('public')->assertMissing($oldImage2);
    }

    public function test_gets_storage_stats()
    {
        $user = User::factory()->create([
            'user_type' => User::TYPE_SOLO_HANDYMAN,
        ]);

        // Store some images
        $image1 = UploadedFile::fake()->image('stats-test-1.jpg', 400, 300);
        $image2 = UploadedFile::fake()->image('stats-test-2.png', 800, 600);

        DiscoveryImageService::storeDiscoveryImage($image1, $user);
        DiscoveryImageService::storeDiscoveryImage($image2, $user);

        $stats = DiscoveryImageService::getStorageStats($user);

        $this->assertArrayHasKey('total_files', $stats);
        $this->assertArrayHasKey('total_size', $stats);
        $this->assertArrayHasKey('months', $stats);

        $this->assertEquals(2, $stats['total_files']);
        $this->assertGreaterThan(0, $stats['total_size']);

        $currentMonth = strtolower(now()->format('F-Y'));
        $this->assertArrayHasKey($currentMonth, $stats['months']);
        $this->assertEquals(2, $stats['months'][$currentMonth]['files']);
    }

    public function test_handles_company_employee_like_admin()
    {
        $company = Company::factory()->create(['name' => 'Employee Company']);
        $employee = User::factory()->create([
            'user_type' => User::TYPE_COMPANY_EMPLOYEE,
            'company_id' => $company->id,
        ]);

        $image = UploadedFile::fake()->image('employee-image.jpg');
        $storedPath = DiscoveryImageService::storeDiscoveryImage($image, $employee);

        // Should use company folder structure
        $expectedPathPattern = "discoveries/employee-company-{$company->id}/";
        $this->assertStringStartsWith($expectedPathPattern, $storedPath);
    }

    public function test_gets_image_url()
    {
        $user = User::factory()->create([
            'user_type' => User::TYPE_SOLO_HANDYMAN,
        ]);

        $image = UploadedFile::fake()->image('url-test.jpg');
        $storedPath = DiscoveryImageService::storeDiscoveryImage($image, $user);

        $url = DiscoveryImageService::getImageUrl($storedPath);

        $this->assertStringStartsWith('/storage/', $url);
        $this->assertStringContainsString($storedPath, $url);
    }
}
