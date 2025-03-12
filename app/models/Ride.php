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
               v.eco_friendly
               FROM rides r 
               JOIN users u ON r.driver_id = u.id 
               JOIN vehicles v ON r.vehicle_id = v.id
               WHERE r.id = ? 
               GROUP BY r.id, u.first_name, u.last_name, u.email, v.brand, v.eco_friendly, u.profile_image
               LIMIT 1";
        return $this->db->fetch($sql, [$id]);
    }
    
    /**
     * Search for rides
     * 
     * The search criteria are:
     * - Departure location
     * - Arrival location
     * - Date of travel
     * 
     * The list of rides is ordered by departure time. 
     * The list of rides is filtered by the number of available seats.
     * The list of rides is returned in a timerange of 1 hour from the departure time.(If the ride is passed the hour, it is not returned)
     * 
     * @param string $departure Departure location
     * @param string $arrival Arrival location
     * @param string $date Date of travel
     * @return array
     */
    public function search($departure, $arrival, $date) {
        $sql = "SELECT r.*,
               CONCAT(u.first_name,'', u.last_name) as driver_name,
               r.available_seats as seats_available,
               v.brand as vehicle_type,
               v.eco_friendly,
               u.profile_image
               FROM rides r
               JOIN users u ON r.driver_id = u.id
               JOIN vehicles v ON r.vehicle_id = v.id
               WHERE r.departure_location =?
               AND r.arrival_location =?
               AND r.departure_time >=?
               AND r.departure_time <= DATE_ADD(?, INTERVAL 1 HOUR)
               AND r.available_seats > 0
               GROUP BY r.id, u.first_name, u.last_name, v.brand, v.eco_friendly, u.profile_image
               ORDER BY r.departure_time ASC";

        return $this->db->fetchAll($sql, [$departure, $arrival, $date, $date]);
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
}