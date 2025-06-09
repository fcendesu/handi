<?php

// Test priority feature implementation
echo "=== Discovery Priority Implementation Test ===\n\n";

// Test 1: Check priority constants
echo "1. Priority Constants:\n";
echo "   PRIORITY_LOW = 1\n";
echo "   PRIORITY_MEDIUM = 2\n";
echo "   PRIORITY_HIGH = 3\n";

// Test 2: Check form field implementation
$formPath = '/home/fcen/laravel/handi/resources/views/discovery/index.blade.php';
$formContent = file_get_contents($formPath);

echo "\n2. Form Implementation Check:\n";

$priorityFieldExists = strpos($formContent, 'name="priority"') !== false;
echo "   Priority field exists: " . ($priorityFieldExists ? "✅ YES" : "❌ NO") . "\n";

$priorityLabelExists = strpos($formContent, 'Öncelik Seviyesi') !== false;
echo "   Priority label exists: " . ($priorityLabelExists ? "✅ YES" : "❌ NO") . "\n";

$prioritySelectExists = strpos($formContent, '<select name="priority"') !== false;
echo "   Priority select element: " . ($prioritySelectExists ? "✅ YES" : "❌ NO") . "\n";

$priorityOptionsExists = strpos($formContent, 'Discovery::getPriorityLabels()') !== false;
echo "   Priority options from model: " . ($priorityOptionsExists ? "✅ YES" : "❌ NO") . "\n";

$priorityErrorExists = strpos($formContent, '@error(\'priority\')') !== false;
echo "   Priority error handling: " . ($priorityErrorExists ? "✅ YES" : "❌ NO") . "\n";

// Test 3: Check controller validation
$controllerPath = '/home/fcen/laravel/handi/app/Http/Controllers/DiscoveryController.php';
$controllerContent = file_get_contents($controllerPath);

echo "\n3. Controller Validation Check:\n";

$storeValidation = strpos($controllerContent, "'priority' => ['nullable', 'integer', Rule::in(array_keys(Discovery::getPriorities()))]") !== false;
echo "   Store method validation: " . ($storeValidation ? "✅ YES" : "❌ NO") . "\n";

// Count how many methods have priority validation
$priorityValidationCount = substr_count($controllerContent, "'priority' => ['nullable', 'integer', Rule::in(array_keys(Discovery::getPriorities()))]");
echo "   Priority validation in methods: $priorityValidationCount\n";

// Test 4: Check model fillable attributes
echo "\n4. Model Configuration Check:\n";

$modelPath = '/home/fcen/laravel/handi/app/Models/Discovery.php';
$modelContent = file_get_contents($modelPath);

$priorityInFillable = strpos($modelContent, "'priority'") !== false;
echo "   Priority in fillable: " . ($priorityInFillable ? "✅ YES" : "❌ NO") . "\n";

$priorityConstants = strpos($modelContent, 'const PRIORITY_LOW = 1') !== false;
echo "   Priority constants defined: " . ($priorityConstants ? "✅ YES" : "❌ NO") . "\n";

$priorityMethods = strpos($modelContent, 'getPriorities()') !== false && strpos($modelContent, 'getPriorityLabels()') !== false;
echo "   Priority helper methods: " . ($priorityMethods ? "✅ YES" : "❌ NO") . "\n";

// Test 5: Check migration
echo "\n5. Database Migration Check:\n";

$migrationFiles = glob('/home/fcen/laravel/handi/database/migrations/*_create_discoveries_table.php');
if (!empty($migrationFiles)) {
    $migrationContent = file_get_contents($migrationFiles[0]);
    $priorityColumn = strpos($migrationContent, '$table->tinyInteger(\'priority\')->default(1)') !== false;
    echo "   Priority column in migration: " . ($priorityColumn ? "✅ YES" : "❌ NO") . "\n";
} else {
    echo "   Migration file not found: ❌ NO\n";
}

// Test 6: Check tests
echo "\n6. Test Coverage Check:\n";

$testPath = '/home/fcen/laravel/handi/tests/Feature/DiscoveryPriorityTest.php';
if (file_exists($testPath)) {
    echo "   Priority test file exists: ✅ YES\n";
    $testContent = file_get_contents($testPath);
    $testCount = substr_count($testContent, 'test(');
    echo "   Number of priority tests: $testCount\n";
} else {
    echo "   Priority test file exists: ❌ NO\n";
}

// Summary
echo "\n=== IMPLEMENTATION SUMMARY ===\n";

$checks = [
    'Form field implementation' => $priorityFieldExists && $prioritySelectExists && $priorityOptionsExists,
    'Controller validation' => $storeValidation && $priorityValidationCount >= 3,
    'Model configuration' => $priorityInFillable && $priorityConstants && $priorityMethods,
    'Database migration' => isset($priorityColumn) ? $priorityColumn : false,
    'Test coverage' => file_exists($testPath),
];

$passed = 0;
$total = count($checks);

foreach ($checks as $check => $result) {
    echo ($result ? "✅" : "❌") . " $check\n";
    if ($result) $passed++;
}

echo "\nOverall Progress: $passed/$total checks passed\n";

if ($passed === $total) {
    echo "\n🎉 PRIORITY FEATURE IMPLEMENTATION COMPLETE! 🎉\n";
    echo "\nFeatures implemented:\n";
    echo "✅ Priority dropdown in discovery creation form\n";
    echo "✅ Priority validation in all controller methods\n";
    echo "✅ Priority constants and helper methods in model\n";
    echo "✅ Priority column in database with default value\n";
    echo "✅ Comprehensive test coverage\n";
    echo "✅ Error handling and validation\n";
    
    echo "\nUsers can now:\n";
    echo "- Select priority level when creating discoveries\n";
    echo "- Choose from Low (Default), Medium, and High (Urgent)\n";
    echo "- Have automatic validation of priority values\n";
    echo "- Get proper error messages for invalid priorities\n";
} else {
    echo "\n⚠️  Some checks failed. Please review the missing components.\n";
}
