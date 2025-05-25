<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

// Get ride ID from query string
$rideId = $_GET['ride_id'] ?? null;

if (!$rideId) {
    echo "event: error\n";
    echo "data: Missing ride ID\n\n";
    exit;
}

// Define the path for the status file
$statusFile = __DIR__ . "/../data/ride_status_{$rideId}.txt";

// Check if this is the first access or if the status has changed
$lastModified = isset($_SERVER['HTTP_LAST_EVENT_ID']) ? $_SERVER['HTTP_LAST_EVENT_ID'] : 0;
$currentModified = file_exists($statusFile) ? filemtime($statusFile) : 0;

// Create the data directory if it doesn't exist
if (!file_exists(__DIR__ . "/../data/")) {
    mkdir(__DIR__ . "/../data/", 0755, true);
}

// Connect to the database to get initial status
require_once __DIR__ . '/../app/config/Database.php';
$db = \App\Config\Database::getInstance();

$sql = "SELECT status FROM rides WHERE id = ?";
$result = $db->fetch($sql, [$rideId]);
$status = $result ? $result['status'] : 'unknown';

// If status file doesn't exist, create it with current status
if (!file_exists($statusFile)) {
    file_put_contents($statusFile, $status);
    $currentModified = time();
}

// Send the current status immediately
echo "id: " . time() . "\n";
echo "event: status\n";
echo "data: " . json_encode(['status' => $status]) . "\n\n";
flush();

// Keep the connection open and check for updates
$lastCheck = time();
$timeout = 30; // 30 seconds timeout

while (true) {
    // Check if the status file has been modified
    clearstatcache();
    if (file_exists($statusFile)) {
        $newModified = filemtime($statusFile);
        if ($newModified > $currentModified) {
            $status = file_get_contents($statusFile);
            echo "id: " . time() . "\n";
            echo "event: status\n";
            echo "data: " . json_encode(['status' => $status]) . "\n\n";
            flush();
            $currentModified = $newModified;
        }
    }
    
    // Send a ping every 15 seconds to keep the connection alive
    if (time() - $lastCheck >= 15) {
        echo "event: ping\n";
        echo "data: " . time() . "\n\n";
        flush();
        $lastCheck = time();
    }
    
    // Check for timeout
    if (time() - $lastCheck >= $timeout) {
        break;
    }
    
    // Sleep for a short time to avoid CPU usage
    usleep(500000); // 0.5 seconds
}

// Close the connection
echo "event: close\n";
echo "data: Timeout\n\n"; 