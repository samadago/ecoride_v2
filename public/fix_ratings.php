<?php
// Fix ratings script - accessible via web
define('BASE_PATH', dirname(__DIR__));

// Simple autoloader
spl_autoload_register(function ($class) {
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $namespaceMap = [
        'App\\Models\\' => BASE_PATH . '/app/models/',
        'App\\Config\\' => BASE_PATH . '/app/config/',
    ];
    
    foreach ($namespaceMap as $namespace => $directory) {
        if (strpos($class, $namespace) === 0) {
            $classPath = $directory . substr($class, strlen($namespace)) . '.php';
            if (file_exists($classPath)) {
                require_once $classPath;
                return true;
            }
        }
    }
    return false;
});

echo "<h1>Rating System Fix</h1>";

try {
    $db = \App\Config\Database::getInstance();
    
    // Check current ratings
    echo "<h2>1. Current Ratings Status</h2>";
    $total = $db->fetch("SELECT COUNT(*) as count FROM ratings");
    echo "Total ratings: " . ($total['count'] ?? 0) . "<br>";
    
    $statusCounts = $db->fetchAll("SELECT status, COUNT(*) as count FROM ratings GROUP BY status");
    if ($statusCounts) {
        echo "By status:<br>";
        foreach ($statusCounts as $status) {
            echo "- " . $status['status'] . ": " . $status['count'] . "<br>";
        }
    } else {
        echo "No ratings found<br>";
    }
    
    // Auto-approve all pending ratings
    echo "<h2>2. Auto-approving Pending Ratings</h2>";
    $updated = $db->query(
        "UPDATE ratings SET status = 'approved', admin_id = 1, moderated_at = NOW(), admin_notes = 'Auto-approved for testing' WHERE status = 'pending'"
    );
    
    if ($updated) {
        echo "✅ Successfully auto-approved pending ratings<br>";
    } else {
        echo "⚠️ No pending ratings found to approve<br>";
    }
    
    // Check after approval
    echo "<h2>3. After Approval</h2>";
    $statusCounts = $db->fetchAll("SELECT status, COUNT(*) as count FROM ratings GROUP BY status");
    if ($statusCounts) {
        echo "By status:<br>";
        foreach ($statusCounts as $status) {
            echo "- " . $status['status'] . ": " . $status['count'] . "<br>";
        }
    }
    
    // Test user ratings
    echo "<h2>4. User Rating Tests</h2>";
    require_once BASE_PATH . '/app/models/User.php';
    $userModel = new \App\Models\User();
    
    for ($i = 1; $i <= 3; $i++) {
        $rating = $userModel->getUserRating($i);
        echo "User $i: Average=" . number_format($rating['average_rating'] ?? 0, 1) . 
             ", Count=" . ($rating['rating_count'] ?? 0) . "<br>";
    }
    
    echo "<h2>✅ Fix Complete!</h2>";
    echo "<p><strong>You can now:</strong></p>";
    echo "<ul>";
    echo "<li>Check <a href='/admin/reviews' target='_blank'>Admin Reviews</a> (should now work)</li>";
    echo "<li>Check user profiles to see ratings displayed</li>";
    echo "<li>Create new reviews - they'll go to pending and need admin approval</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error</h2>";
    echo "Error: " . $e->getMessage() . "<br>";
    echo "Details: " . nl2br($e->getTraceAsString());
}
?> 