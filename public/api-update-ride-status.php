<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set header to return JSON
header('Content-Type: application/json');

// Start the session to access user info
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

// Get the current user
$currentUser = $_SESSION['user'];

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get the ride ID and new status from the request
$rideId = $_POST['ride_id'] ?? null;
$newStatus = $_POST['status'] ?? null;

if (!$rideId || !$newStatus) {
    echo json_encode(['error' => 'Missing ride ID or status']);
    exit;
}

// Validate the status
$validStatuses = ['pending', 'ongoing', 'completed', 'cancelled'];
if (!in_array($newStatus, $validStatuses)) {
    echo json_encode(['error' => 'Invalid status']);
    exit;
}

// Load the database connection
require_once __DIR__ . '/../app/config/Database.php';
$db = \App\Config\Database::getInstance();

// Check if the user is authorized to update this ride
// User must be the driver, a passenger, or an admin
$sql = "SELECT r.driver_id, 
               (SELECT COUNT(*) FROM bookings WHERE ride_id = r.id AND passenger_id = ? AND status = 'confirmed') as is_passenger,
               ? as is_admin
        FROM rides r 
        WHERE r.id = ?";

$result = $db->fetch($sql, [$currentUser['id'], $currentUser['is_admin'] ?? 0, $rideId]);

if (!$result) {
    echo json_encode(['error' => 'Ride not found']);
    exit;
}

$isAuthorized = ($result['driver_id'] == $currentUser['id']) || 
                ($result['is_passenger'] > 0) || 
                ($result['is_admin'] > 0);

if (!$isAuthorized) {
    echo json_encode(['error' => 'Not authorized to update this ride']);
    exit;
}

// Get the current status
$currentStatusSql = "SELECT status FROM rides WHERE id = ?";
$currentStatusResult = $db->fetch($currentStatusSql, [$rideId]);
$currentStatus = $currentStatusResult ? $currentStatusResult['status'] : '';

// Start a transaction
try {
    $db->beginTransaction();
    
    // Update the ride status in the database
    $updateSql = "UPDATE rides SET status = ? WHERE id = ?";
    $success = $db->query($updateSql, [$newStatus, $rideId]);
    
    if (!$success) {
        throw new \Exception('Failed to update ride status');
    }
    
    // Special handling for status changes
    if ($currentStatus != $newStatus) {
        // Required classes for processing
        require_once __DIR__ . '/../app/models/User.php';
        require_once __DIR__ . '/../app/models/Booking.php';
        require_once __DIR__ . '/../app/models/Ride.php';
        require_once __DIR__ . '/../app/models/CreditTransaction.php';
        
        $userModel = new \App\Models\User();
        $bookingModel = new \App\Models\Booking();
        $rideModel = new \App\Models\Ride();
        
        // Get ride details
        $ride = $rideModel->getById($rideId);
        $driverId = $ride['driver_id'];
        
        // Handle ride completion - transfer credits to driver
        if ($newStatus === 'completed') {
            // Get ALL bookings for this ride (not just confirmed ones)
            $bookingsSql = "SELECT b.*, 
                           u.first_name, 
                           u.last_name, 
                           b.seats_booked,
                           r.price  
                           FROM bookings b 
                           JOIN users u ON b.passenger_id = u.id 
                           JOIN rides r ON b.ride_id = r.id
                           WHERE b.ride_id = ? AND b.status != 'cancelled'";
            $bookings = $db->fetchAll($bookingsSql, [$rideId]);
            
            // Debug all bookings found regardless of status
            error_log("Found " . count($bookings) . " total bookings for ride {$rideId}");
            foreach ($bookings as $b) {
                error_log("Booking ID: {$b['id']}, Passenger: {$b['first_name']} {$b['last_name']}, Status: {$b['status']}, Seats: {$b['seats_booked']}");
            }
            
            $totalEarnings = 0;
            
            // Process each booking - PAY FOR ALL NON-CANCELLED BOOKINGS
            foreach ($bookings as $booking) {
                // Calculate earnings for this booking
                $bookingPrice = $ride['price'] * $booking['seats_booked'];
                $totalEarnings += $bookingPrice;
                
                // Update booking status to completed
                $bookingModel->updateStatus($booking['id'], 'completed');
                
                // Debug output
                error_log("Processing booking {$booking['id']} for ride {$rideId}");
                error_log("Seats booked: {$booking['seats_booked']}, Price per seat: {$ride['price']}, Total: {$bookingPrice}");
            }
            
            // Add credits to driver
            if ($totalEarnings > 0) {
                $description = "Revenus pour trajet complété de {$ride['departure_location']} à {$ride['arrival_location']}";
                $success = $userModel->adjustCredit($driverId, $totalEarnings, 'ride_earnings', $rideId, $description);
                
                // Debug output
                error_log("Adding {$totalEarnings}€ to driver {$driverId} for ride {$rideId}: " . ($success ? 'Success' : 'Failed'));
            } else {
                // Debug output
                error_log("No earnings for driver {$driverId} for ride {$rideId} - No bookings found");
            }
        }
        
        // Handle ride cancellation - refund all passengers
        else if ($newStatus === 'cancelled' && $currentStatus !== 'completed') {
            // Get all non-cancelled bookings for this ride
            $bookingsSql = "SELECT b.* FROM bookings b WHERE b.ride_id = ? AND b.status != 'cancelled'";
            $bookings = $db->fetchAll($bookingsSql, [$rideId]);
            
            // Process each booking
            foreach ($bookings as $booking) {
                // Calculate refund amount (100% refund for ride cancellation)
                $refundAmount = $ride['price'] * $booking['seats_booked'];
                
                // Update booking status to cancelled
                $bookingModel->updateStatus($booking['id'], 'cancelled');
                
                // Refund passenger
                $description = "Remboursement pour annulation de trajet de {$ride['departure_location']} à {$ride['arrival_location']}";
                $userModel->adjustCredit($booking['passenger_id'], $refundAmount, 'cancellation', $booking['id'], $description, $driverId);
            }
        }
    }
    
    // Commit the transaction
    $db->commit();
    
    // Update the status file for real-time notifications
    $statusFile = __DIR__ . "/../data/ride_status_{$rideId}.txt";
    file_put_contents($statusFile, $newStatus);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Ride status updated successfully',
        'ride_id' => $rideId,
        'status' => $newStatus
    ]);
} catch (\Exception $e) {
    $db->rollBack();
    echo json_encode(['error' => 'Error updating ride status: ' . $e->getMessage()]);
    exit;
} 