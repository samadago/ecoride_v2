<?php
/**
 * Deployment Script
 * 
 * This script is run during deployment to set up the database and add the default admin user.
 */

// Include database configuration
require_once __DIR__ . '/../app/config/Database.php';

// Get database connection
$db = \App\Config\Database::getInstance()->getConnection();

echo "Starting deployment process...\n";

// Function to run SQL file
function runSqlFile($db, $filePath) {
    echo "Running SQL file: $filePath\n";
    
    $sql = file_get_contents($filePath);
    
    if (!$sql) {
        echo "Error reading file: $filePath\n";
        return false;
    }
    
    try {
        // Execute the SQL
        $result = $db->exec($sql);
        echo "SQL file executed successfully.\n";
        return true;
    } catch (PDOException $e) {
        echo "Error executing SQL: " . $e->getMessage() . "\n";
        return false;
    }
}

// Run schema if needed (first deployment)
echo "Checking if database schema needs to be created...\n";
try {
    // Check if users table exists
    $stmt = $db->query("SHOW TABLES LIKE 'users'");
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        echo "Database schema not found. Creating tables...\n";
        runSqlFile($db, __DIR__ . '/../database/schema.sql');
    } else {
        echo "Database schema already exists.\n";
    }
} catch (PDOException $e) {
    echo "Error checking database schema: " . $e->getMessage() . "\n";
    exit(1);
}

// Run migrations
echo "Running migrations...\n";

// Run all migration files from the migrations directory
$migrations = glob(__DIR__ . '/../database/migrations/*.sql');
foreach ($migrations as $migration) {
    echo "Running migration: " . basename($migration) . "\n";
    try {
        $sql = file_get_contents($migration);
        $db->exec($sql);
        echo "Migration completed successfully.\n";
    } catch (PDOException $e) {
        echo "Error running migration: " . $e->getMessage() . "\n";
    }
}

echo "Deployment completed successfully!\n"; 