<?php
/**
 * Fix Missing Tables Script
 * 
 * This script checks for and creates missing tables required by the system.
 */

// Include database configuration
require_once __DIR__ . '/../app/config/Database.php';

// Get database connection
$db = \App\Config\Database::getInstance()->getConnection();

echo "Checking for missing tables...\n";

// Check if credit_transactions table exists
try {
    $stmt = $db->query("SHOW TABLES LIKE 'credit_transactions'");
    $transactionsTableExists = $stmt->rowCount() > 0;
    
    if (!$transactionsTableExists) {
        echo "credit_transactions table doesn't exist. Creating...\n";
        
        $sql = "CREATE TABLE IF NOT EXISTS credit_transactions (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            related_user_id INT NULL,
            amount DECIMAL(10,2) NOT NULL,
            type ENUM('booking', 'cancellation', 'ride_earnings', 'admin_adjustment', 'credit_request') NOT NULL,
            reference_id INT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (related_user_id) REFERENCES users(id) ON DELETE SET NULL
        )";
        
        $db->exec($sql);
        echo "credit_transactions table created successfully.\n";
    } else {
        echo "credit_transactions table already exists.\n";
    }
    
    // Check if credit_requests table exists
    $stmt = $db->query("SHOW TABLES LIKE 'credit_requests'");
    $requestsTableExists = $stmt->rowCount() > 0;
    
    if (!$requestsTableExists) {
        echo "credit_requests table doesn't exist. Creating...\n";
        
        $sql = "CREATE TABLE IF NOT EXISTS credit_requests (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            admin_id INT NULL,
            reason TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL
        )";
        
        $db->exec($sql);
        echo "credit_requests table created successfully.\n";
    } else {
        echo "credit_requests table already exists.\n";
    }
    
    // Update users table to have default credit of 200 if needed
    $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'credit'");
    $creditColumnExists = $stmt->rowCount() > 0;
    
    if (!$creditColumnExists) {
        echo "credit column doesn't exist in users table. Adding...\n";
        
        $sql = "ALTER TABLE users ADD COLUMN credit DECIMAL(10,2) DEFAULT 200.00";
        $db->exec($sql);
        echo "credit column added to users table.\n";
    } else {
        echo "Updating default credit to 200...\n";
        $sql = "ALTER TABLE users MODIFY COLUMN credit DECIMAL(10,2) DEFAULT 200.00";
        $db->exec($sql);
        
        // Update existing users who have the default 100 credit
        $sql = "UPDATE users SET credit = 200.00 WHERE credit = 100.00";
        $affected = $db->exec($sql);
        echo "Updated $affected users with default credit.\n";
    }
    
    echo "\nAll tables and columns have been checked and fixed.\n";
    echo "You should now be able to access the profile page without errors.\n";
    
} catch (\PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 