<?php

namespace App\Models;

use App\Config\Database;

class Ride {
    private $db;
    
    // Define properties to avoid dynamic property creation
    public $id;
    public $driver_id;
    public $user_id;
    public $vehicle_id;
    public $departure_location;
    public $arrival_location;
    public $departure_time;
    public $estimated_arrival_time;
    public $available_seats;
    public $seats_available;
    public $price;
    public $eco_friendly;
    public $description;
    public $status;
    public $created_at;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function updateRemainingSeats($rideId) {
        // Get the total seats and count of confirmed bookings
        $sql = "SELECT r.total_seats, COUNT(b.id) as booked_seats 
                FROM rides r 
                LEFT JOIN bookings b ON r.id = b.ride_id 
                WHERE r.id = :ride_id AND b.status = 'confirmed' 
                GROUP BY r.id, r.total_seats";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['ride_id' => $rideId]);
        $result = $stmt->fetch();
        
        // Calculate remaining seats
        $totalSeats = $result['total_seats'] ?? 0;
        $bookedSeats = $result['booked_seats'] ?? 0;
        $remainingSeats = max(0, $totalSeats - $bookedSeats);
        
        // Update the remaining_seats in rides table
        $updateSql = "UPDATE rides SET remaining_seats = :remaining_seats WHERE id = :ride_id";
        $updateStmt = $this->db->prepare($updateSql);
        return $updateStmt->execute([
            'remaining_seats' => $remainingSeats,
            'ride_id' => $rideId
        ]);
    }
    
    /**
     * Get all rides
     * 
     * @return array
     */
    public function getAllWithVehicles() {
        $sql = "SELECT r.*, 
               CONCAT(u.first_name, ' ', u.last_name) as driver_name,
               r.available_seats as seats_available,
               v.brand as vehicle_type,
               v.eco_friendly,
               u.profile_image
               FROM rides r 
               JOIN users u ON r.driver_id = u.id 
               JOIN vehicles v ON r.vehicle_id = v.id
               GROUP BY r.id, u.first_name, u.last_name, v.brand, v.eco_friendly, u.profile_image
               ORDER BY r.departure_time ASC";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get a ride by ID
     * 
     * @param int $id Ride ID
     * @return array|false
     */
    public function getById($id) {
        $sql = "SELECT r.*, 
               CONCAT(u.first_name, ' ', u.last_name) as driver_name, 
               u.email as driver_email,
               u.id as user_id,
               u.profile_image,
               r.available_seats as seats_available,
               (r.available_seats - COALESCE((SELECT COUNT(*) FROM bookings b WHERE b.ride_id = r.id AND b.status = 'confirmed'), 0)) as remaining_seats,
               v.brand as vehicle_type,
               v.eco_friendly,
               r.status
               FROM rides r 
               JOIN users u ON r.driver_id = u.id 
               JOIN vehicles v ON r.vehicle_id = v.id
               WHERE r.id = ? 
               GROUP BY r.id, u.first_name, u.last_name, u.email, v.brand, v.eco_friendly, u.profile_image, r.status
               LIMIT 1";
        return $this->db->fetch($sql, [$id]);
    }
    
    /**
     * Search for rides
     * 
     * The search criteria are:
     * - Departure location (partial match)
     * - Arrival location (partial match)
     * - Date of travel (day-based search with flexible time)
     * 
     * The list of rides is ordered by departure time. 
     * The list of rides is filtered by the number of available seats.
     * Only returns rides that haven't departed yet.
     * 
     * @param string $departure Departure location
     * @param string $arrival Arrival location
     * @param string $date Date of travel (can be date or datetime)
     * @return array
     */
    public function search($departure, $arrival, $date) {
        // Clean the input parameters
        $departure = trim($departure);
        $arrival = trim($arrival);
        $date = trim($date);
        
        // Convert datetime-local format to date if needed
        $searchDate = null;
        if (!empty($date)) {
            if (strpos($date, 'T') !== false) {
                // Full datetime provided - use it for more precise filtering
                $searchDate = date('Y-m-d H:i:s', strtotime($date));
            } else {
                // Just date provided - search for that entire day
                $searchDate = date('Y-m-d', strtotime($date));
            }
        }
        
        // Base SQL query
        $sql = "SELECT r.*,
               CONCAT(u.first_name, ' ', u.last_name) as driver_name,
               r.available_seats as seats_available,
               v.brand as vehicle_type,
               v.eco_friendly,
               u.profile_image
               FROM rides r
               JOIN users u ON r.driver_id = u.id
               JOIN vehicles v ON r.vehicle_id = v.id
               WHERE r.available_seats > 0
               AND r.status IN ('pending', 'ongoing')
               AND r.departure_time > NOW()";
        
        $params = [];
        
        // Add departure location filter with partial matching
        if (!empty($departure)) {
            $sql .= " AND r.departure_location LIKE ?";
            $params[] = '%' . $departure . '%';
        }
        
        // Add arrival location filter with partial matching
        if (!empty($arrival)) {
            $sql .= " AND r.arrival_location LIKE ?";
            $params[] = '%' . $arrival . '%';
        }
        
        // Add date filter
        if (!empty($searchDate)) {
            if (strpos($date, 'T') !== false) {
                // Specific datetime - find rides departing after this time
                $sql .= " AND r.departure_time >= ?";
                $params[] = $searchDate;
            } else {
                // Just date - find rides on this specific day
                $sql .= " AND DATE(r.departure_time) = ?";
                $params[] = $searchDate;
            }
        }
        
        // Group by ride to avoid duplicates and order by departure time
        $sql .= " GROUP BY r.id, u.first_name, u.last_name, v.brand, v.eco_friendly, u.profile_image
                 ORDER BY r.departure_time ASC";

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Flexible search for rides with optional filters
     * 
     * @param array $filters Array of search filters
     * @return array
     */
    public function flexibleSearch($filters = []) {
        // Base SQL query
        $sql = "SELECT r.*,
               CONCAT(u.first_name, ' ', u.last_name) as driver_name,
               r.available_seats as seats_available,
               v.brand as vehicle_type,
               v.eco_friendly,
               u.profile_image
               FROM rides r
               JOIN users u ON r.driver_id = u.id
               JOIN vehicles v ON r.vehicle_id = v.id
               WHERE r.available_seats > 0
               AND r.status IN ('pending', 'ongoing')
               AND r.departure_time > NOW()";
        
        $params = [];
        
        // Add departure location filter if provided
        if (!empty($filters['departure'])) {
            $sql .= " AND r.departure_location LIKE ?";
            $params[] = '%' . trim($filters['departure']) . '%';
        }
        
        // Add arrival location filter if provided
        if (!empty($filters['arrival'])) {
            $sql .= " AND r.arrival_location LIKE ?";
            $params[] = '%' . trim($filters['arrival']) . '%';
        }
        
        // Add date filter if provided
        if (!empty($filters['date'])) {
            $date = trim($filters['date']);
            // Convert datetime-local format to date if needed
            if (strpos($date, 'T') !== false) {
                $date = date('Y-m-d', strtotime($date));
            }
            $sql .= " AND DATE(r.departure_time) = ?";
            $params[] = $date;
        }
        
        // Add price range filter if provided
        if (!empty($filters['max_price'])) {
            $sql .= " AND r.price <= ?";
            $params[] = floatval($filters['max_price']);
        }
        
        // Add minimum seats filter if provided
        if (!empty($filters['min_seats'])) {
            $sql .= " AND r.available_seats >= ?";
            $params[] = intval($filters['min_seats']);
        }
        
        // Add eco-friendly filter if requested
        if (!empty($filters['eco_only'])) {
            $sql .= " AND v.eco_friendly = 1";
        }
        
        // Group by ride to avoid duplicates and order by departure time
        $sql .= " GROUP BY r.id, u.first_name, u.last_name, v.brand, v.eco_friendly, u.profile_image
                 ORDER BY r.departure_time ASC";
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get upcoming rides (rides that haven't started yet)
     * 
     * @param int $limit Number of rides to return
     * @return array
     */
    public function getUpcomingRides($limit = 10) {
        $sql = "SELECT r.*,
               CONCAT(u.first_name, ' ', u.last_name) as driver_name,
               r.available_seats as seats_available,
               v.brand as vehicle_type,
               v.eco_friendly,
               u.profile_image
               FROM rides r
               JOIN users u ON r.driver_id = u.id
               JOIN vehicles v ON r.vehicle_id = v.id
               WHERE r.available_seats > 0
               AND r.status IN ('pending', 'ongoing')
               AND r.departure_time > NOW()
               GROUP BY r.id, u.first_name, u.last_name, v.brand, v.eco_friendly, u.profile_image
               ORDER BY r.departure_time ASC
               LIMIT ?";
        
        return $this->db->fetchAll($sql, [$limit]);
    }

    /**
     * Create a new ride
     * 
     * @param array $data Ride data
     * @return bool|int
     */
    public function create($data) {
        $sql = "INSERT INTO rides (driver_id, vehicle_id, departure_location, arrival_location, 
                                 departure_time, estimated_arrival_time, available_seats, 
                                 price, description, status, created_at) 
               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
        
        try {
            $this->db->query($sql, [
                $data['user_id'], // driver_id
                $data['vehicle_id'],
                $data['departure_location'],
                $data['arrival_location'],
                $data['departure_time'],
                $data['estimated_arrival_time'],
                $data['seats_available'], // available_seats
                $data['price'],
                $data['description'] ?? ''
            ]);
            
            return $this->db->lastInsertId();
        } catch (\PDOException $e) {
            return false;
        }
    }
    
    /**
     * Save the current ride object to the database
     * 
     * @return bool|int
     */
    public function save() {
        // Create an array from the object properties
        $data = [
            'user_id' => $this->user_id,
            'vehicle_id' => $this->vehicle_id,
            'departure_location' => $this->departure_location,
            'arrival_location' => $this->arrival_location,
            'departure_time' => $this->departure_time,
            'estimated_arrival_time' => $this->estimated_arrival_time,
            'seats_available' => $this->seats_available,
            'price' => $this->price,
            'description' => $this->description ?? '',
            'eco_friendly' => $this->eco_friendly ?? 0
        ];
        
        // Use the existing create method to save the data
        return $this->create($data);
    }
    
    /**
     * Get popular destinations
     * 
     * @param int $limit Number of destinations to return
     * @return array
     */
    public function getPopularDestinations($limit = 3) {
        $sql = "SELECT arrival_location, COUNT(*) as ride_count 
               FROM rides 
               GROUP BY arrival_location 
               ORDER BY ride_count DESC 
               LIMIT ?";
        
        return $this->db->fetchAll($sql, [$limit]);
    }
    
    /**
     * Get rides by user ID
     * 
     * @param int $userId User ID
     * @return array
     */
    public function getByUserId($userId) {
        $sql = "SELECT r.*, 
               CONCAT(u.first_name, ' ', u.last_name) as driver_name,
               r.available_seats as seats_available,
               v.brand as vehicle_type,
               v.eco_friendly
               FROM rides r 
               JOIN users u ON r.driver_id = u.id 
               JOIN vehicles v ON r.vehicle_id = v.id
               WHERE r.driver_id = ? 
               GROUP BY r.id, u.first_name, u.last_name, v.brand, v.eco_friendly
               ORDER BY r.departure_time ASC";
        return $this->db->fetchAll($sql, [$userId]);
    }

    /**
     * Get total number of rides
     * 
     * @return int
     */
    public function getTotalRides() {
        $sql = "SELECT COUNT(*) as total FROM rides";
        $result = $this->db->fetch($sql);
        return $result ? (int)$result['total'] : 0;
    }

    /**
     * Count rides by status
     * 
     * @param string $status
     * @return int
     */
    public function countRidesByStatus($status) {
        $sql = "SELECT COUNT(*) as total FROM rides WHERE status = ?";
        $result = $this->db->fetch($sql, [$status]);
        return $result ? (int)$result['total'] : 0;
    }

    /**
     * Get all rides with details for admin panel
     * 
     * @return array
     */
    public function getAllRidesWithDetails() {
        $sql = "SELECT r.*, 
                    u.first_name AS driver_first_name, 
                    u.last_name AS driver_last_name,
                    v.brand AS vehicle_brand, 
                    v.model AS vehicle_model,
                    (SELECT COUNT(*) FROM bookings WHERE ride_id = r.id AND status != 'cancelled') AS booking_count
                FROM rides r
                JOIN users u ON r.driver_id = u.id
                JOIN vehicles v ON r.vehicle_id = v.id
                ORDER BY r.departure_time DESC";
        
        return $this->db->fetchAll($sql);
    }

    /**
     * Delete a ride
     * 
     * @param int $rideId
     * @return bool
     */
    public function deleteRide($rideId) {
        try {
            $sql = "DELETE FROM rides WHERE id = ?";
            return $this->db->query($sql, [$rideId]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Update ride status
     * 
     * @param int $rideId
     * @param string $status
     * @return bool
     */
    public function updateRideStatus($rideId, $status) {
        if (!in_array($status, ['pending', 'ongoing', 'completed', 'cancelled'])) {
            return false;
        }
        
        try {
            $sql = "UPDATE rides SET status = ? WHERE id = ?";
            return $this->db->query($sql, [$status, $rideId]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Complete a ride
     * 
     * @param int $rideId Ride ID
     * @param int $driverId Driver ID to verify
     * @return bool Success status
     */
    public function completeRide($rideId, $driverId) {
        try {
            // First verify this driver is assigned to this ride
            $ride = $this->getById($rideId);
            if (!$ride || $ride['driver_id'] != $driverId) {
                return false;
            }
            
            // Update ride status
            $sql = "UPDATE rides SET status = 'completed', updated_at = NOW() WHERE id = ? AND driver_id = ?";
            return $this->db->query($sql, [$rideId, $driverId]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Cancel a ride
     * 
     * @param int $rideId Ride ID
     * @param int $userId User ID (must be the driver)
     * @return bool Success status
     */
    public function cancelRide($rideId, $userId) {
        try {
            // First verify this user is the driver of this ride
            $ride = $this->getById($rideId);
            if (!$ride || $ride['driver_id'] != $userId) {
                return false;
            }
            
            // Update ride status
            $sql = "UPDATE rides SET status = 'cancelled', updated_at = NOW() WHERE id = ? AND driver_id = ?";
            return $this->db->query($sql, [$rideId, $userId]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}