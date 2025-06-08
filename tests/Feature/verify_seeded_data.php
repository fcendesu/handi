#!/usr/bin/env php
<?php

// Simple verification script to check seeded data
require __DIR__ . '/vendor/autoload.php';

// Basic Laravel bootstrap without full app boot
$config = require __DIR__ . '/config/database.php';
$dbConfig = $config['connections']['mysql'];

try {
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset=utf8mb4",
        $dbConfig['username'],
        $dbConfig['password']
    );
    
    echo "🔍 Checking seeded data...\n\n";
    
    // Check users
    $users = $pdo->query("SELECT name, email, user_type, company_id FROM users")->fetchAll(PDO::FETCH_ASSOC);
    echo "👥 USERS:\n";
    foreach ($users as $user) {
        echo "  - {$user['name']} ({$user['email']}) - {$user['user_type']}";
        if ($user['company_id']) {
            echo " [Company: {$user['company_id']}]";
        }
        echo "\n";
    }
    
    echo "\n";
    
    // Check companies
    $companies = $pdo->query("SELECT name, email, admin_id FROM companies")->fetchAll(PDO::FETCH_ASSOC);
    echo "🏢 COMPANIES:\n";
    foreach ($companies as $company) {
        echo "  - {$company['name']} ({$company['email']}) [Admin: {$company['admin_id']}]\n";
    }
    
    echo "\n✅ Data verification completed!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
