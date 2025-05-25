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
        
        // Check if users table is empty
        $stmt = $db->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] == 0) {
            echo "Users table is empty. Force creating database schema...\n";
            runSqlFile($db, __DIR__ . '/../database/schema.sql');
        }
    }
} catch (PDOException $e) {
    echo "Error checking database schema: " . $e->getMessage() . "\n";
    exit(1);
}

// Run migrations
echo "Running migrations...\n";

// Run all migration files from the migrations directory
$migrations = glob(__DIR__ . '/../database/migrations/*.sql');
natsort($migrations); // Sort migrations by name to ensure they run in order

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

// Verify admin user exists
echo "Verifying admin user exists...\n";
try {
    $stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE is_admin = 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] == 0) {
        echo "No admin user found. Creating default admin...\n";
        
        // Admin credentials
        $adminEmail = 'admin@ecoride.space';
        $adminPassword = 'EcoRide@Admin2023!';
        
        // Generate password hash dynamically
        $passwordHash = password_hash($adminPassword, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (email, password_hash, first_name, last_name, is_admin, credit, created_at) 
                VALUES ('admin@ecoride.space', 
                        ?, 
                        'Admin', 'EcoRide', 1, 200.00, NOW())";
                        
        $stmt = $db->prepare($sql);
        $stmt->execute([$passwordHash]);
        
        echo "Default admin user created (admin@ecoride.space / EcoRide@Admin2023!).\n";
        
        // Test the password verification
        echo "Testing password verification: ";
        if (password_verify($adminPassword, $passwordHash)) {
            echo "PASSED ✓\n";
        } else {
            echo "FAILED ✗\n";
        }
    } else {
        echo "Admin user already exists.\n";
    }
} catch (PDOException $e) {
    echo "Error verifying admin user: " . $e->getMessage() . "\n";
}

echo "Deployment completed successfully!\n"; 