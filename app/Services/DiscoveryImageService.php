<?php

namespace App\Services;

use App\Models\User;
use App\Models\Company;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DiscoveryImageService
{
    /**
     * Store an image for a discovery with organized folder structure
     * 
     * @param UploadedFile $image
     * @param User $user
     * @return string The stored file path
     */
    public static function storeDiscoveryImage(UploadedFile $image, User $user): string
    {
        $organizationFolder = self::getOrganizationFolder($user);
        $monthFolder = self::getCurrentMonthFolder();

        // Generate unique filename
        $fileName = self::generateUniqueFileName($image);

        // Construct the full path
        $fullPath = "discoveries/{$organizationFolder}/{$monthFolder}";

        // Store the file
        $storedPath = $image->storeAs($fullPath, $fileName, 'public');

        return $storedPath;
    }

    /**
     * Delete an image from storage
     * 
     * @param string $imagePath
     * @return bool
     */
    public static function deleteDiscoveryImage(string $imagePath): bool
    {
        return Storage::disk('public')->delete($imagePath);
    }

    /**
     * Get the organization folder name based on user type
     * 
     * @param User $user
     * @return string
     */
    private static function getOrganizationFolder(User $user): string
    {
        if ($user->isSoloHandyman()) {
            // For solo handyman: "solo-handyman-{id}"
            return "solo-handyman-{$user->id}";
        } elseif ($user->isCompanyAdmin() || $user->isCompanyEmployee()) {
            // For company users: "{company-name}-{company-id}"
            $company = $user->company;
            if ($company) {
                $cleanCompanyName = self::sanitizeFolderName($company->name);
                return "{$cleanCompanyName}-{$company->id}";
            }
        }

        // Fallback
        return "user-{$user->id}";
    }

    /**
     * Get the current month folder name (e.g., "july-2025")
     * 
     * @return string
     */
    private static function getCurrentMonthFolder(): string
    {
        $now = Carbon::now();
        $monthName = strtolower($now->format('F')); // e.g., "july"
        $year = $now->format('Y'); // e.g., "2025"

        return "{$monthName}-{$year}";
    }

    /**
     * Generate a unique filename for the image
     * 
     * @param UploadedFile $image
     * @return string
     */
    private static function generateUniqueFileName(UploadedFile $image): string
    {
        $timestamp = time();
        $randomString = Str::random(8);
        $extension = $image->getClientOriginalExtension();

        return "{$timestamp}_{$randomString}.{$extension}";
    }

    /**
     * Sanitize folder name to be filesystem-safe
     * 
     * @param string $name
     * @return string
     */
    private static function sanitizeFolderName(string $name): string
    {
        // Convert to lowercase, replace spaces and special chars with hyphens
        $sanitized = strtolower($name);
        $sanitized = preg_replace('/[^a-z0-9]+/', '-', $sanitized);
        $sanitized = trim($sanitized, '-');

        // Limit length to 50 characters
        return Str::limit($sanitized, 50, '');
    }

    /**
     * Get the full storage path for displaying images
     * 
     * @param string $imagePath
     * @return string
     */
    public static function getImageUrl(string $imagePath): string
    {
        return Storage::disk('public')->url($imagePath);
    }

    /**
     * Move existing images to new organized structure
     * This method can be used for migrating old images
     * 
     * @param array $imagePaths
     * @param User $user
     * @return array New image paths
     */
    public static function migrateExistingImages(array $imagePaths, User $user): array
    {
        $newPaths = [];

        foreach ($imagePaths as $oldPath) {
            if (Storage::disk('public')->exists($oldPath)) {
                $organizationFolder = self::getOrganizationFolder($user);
                $monthFolder = self::getCurrentMonthFolder();

                // Get file extension from old path
                $extension = pathinfo($oldPath, PATHINFO_EXTENSION);
                $timestamp = time();
                $randomString = Str::random(8);
                $fileName = "{$timestamp}_{$randomString}.{$extension}";

                $newPath = "discoveries/{$organizationFolder}/{$monthFolder}/{$fileName}";

                // Move the file
                if (Storage::disk('public')->move($oldPath, $newPath)) {
                    $newPaths[] = $newPath;
                } else {
                    // If move fails, keep the old path
                    $newPaths[] = $oldPath;
                }
            }
        }

        return $newPaths;
    }

    /**
     * Get storage statistics for a user/company
     * 
     * @param User $user
     * @return array
     */
    public static function getStorageStats(User $user): array
    {
        $organizationFolder = self::getOrganizationFolder($user);
        $basePath = "discoveries/{$organizationFolder}";

        $files = Storage::disk('public')->allFiles($basePath);

        $stats = [
            'total_files' => count($files),
            'total_size' => 0,
            'months' => []
        ];

        foreach ($files as $file) {
            $size = Storage::disk('public')->size($file);
            $stats['total_size'] += $size;

            // Extract month from path
            $pathParts = explode('/', $file);
            if (count($pathParts) >= 3) {
                $monthFolder = $pathParts[2];
                if (!isset($stats['months'][$monthFolder])) {
                    $stats['months'][$monthFolder] = ['files' => 0, 'size' => 0];
                }
                $stats['months'][$monthFolder]['files']++;
                $stats['months'][$monthFolder]['size'] += $size;
            }
        }

        return $stats;
    }
}
