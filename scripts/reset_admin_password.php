<?php
/**
 * Reset Admin Password Script
 * 
 * This script resets the admin password to a known value.
 */

// Include database configuration
require_once __DIR__ . '/../app/config/Database.php';

// Get database connection
$db = \App\Config\Database::getInstance()->getConnection();

echo "Starting admin password reset...\n";

// Admin credentials
$adminEmail = 'admin@ecoride.space';
$newPassword = 'EcoRide@Admin2023!';

// Generate a new password hash
$passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

echo "Generated new password hash.\n";

// Update the admin password
try {
    // First check if admin exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$adminEmail]);
    $admin = $stmt->fetch(\PDO::FETCH_ASSOC);
    
    if ($admin) {
        // Admin exists, update password
        $updateStmt = $db->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
        $result = $updateStmt->execute([$passwordHash, $adminEmail]);
        
        if ($result) {
            echo "Admin password reset successfully!\n";
            echo "Email: $adminEmail\n";
            echo "Password: $newPassword\n";
        } else {
            echo "Failed to update admin password.\n";
        }
    } else {
        // Admin doesn't exist, create it
        $insertStmt = $db->prepare("INSERT INTO users (email, password_hash, first_name, last_name, is_admin, credit, created_at) 
                                    VALUES (?, ?, 'Admin', 'EcoRide', 1, 200.00, NOW())");
        $result = $insertStmt->execute([$adminEmail, $passwordHash]);
        
        if ($result) {
            echo "Admin user created successfully!\n";
            echo "Email: $adminEmail\n";
            echo "Password: $newPassword\n";
        } else {
            echo "Failed to create admin user.\n";
        }
    }
} catch (\PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Test the password verification
echo "\nTesting password verification...\n";
$testPassword = 'EcoRide@Admin2023!';
if (password_verify($testPassword, $passwordHash)) {
    echo "Password verification PASSED ✓\n";
} else {
    echo "Password verification FAILED ✗\n";
}

echo "\nComplete. You can now log in with:\n";
echo "Email: $adminEmail\n";
echo "Password: $newPassword\n"; 