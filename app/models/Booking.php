<?php

namespace App\Models;

use App\Config\Database;

class Booking {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($bookingData) {
        $sql = "INSERT INTO bookings (ride_id, passenger_id, status, seats_booked, booking_time) 
                VALUES (:ride_id, :passenger_id, :status, :seats_booked, :booking_time)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($bookingData);
    }
    
    public function getById($bookingId) {
        $sql = "SELECT b.*, r.departure, r.arrival, r.departure_time, r.price, 
                       u.name as passenger_name, u.email as passenger_email 
                FROM bookings b 
                JOIN rides r ON b.ride_id = r.id 
                JOIN users u ON b.passenger_id = u.id 
                WHERE b.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $bookingId]);
        return $stmt->fetch();
    }
    
    public function getByPassengerId($passengerId) {
        $sql = "SELECT b.*, r.departure_location, r.arrival_location, r.departure_time, r.price, 
                       CONCAT(u.first_name, ' ', u.last_name) as driver_name, u.email as driver_email 
                FROM bookings b 
                JOIN rides r ON b.ride_id = r.id 
                JOIN users u ON r.driver_id = u.id 
                WHERE b.passenger_id = :passenger_id 
                ORDER BY b.booking_time DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['passenger_id' => $passengerId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function getByDriverId($driverId) {
        $sql = "SELECT b.*, r.departure, r.arrival, r.departure_time, r.price, 
                       u.name as passenger_name, u.email as passenger_email 
                FROM bookings b 
                JOIN rides r ON b.ride_id = r.id 
                JOIN users u ON b.passenger_id = u.id 
                WHERE r.user_id = :driver_id 
                ORDER BY b.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['driver_id' => $driverId]);
        return $stmt->fetchAll();
    }
    
    public function updateStatus($bookingId, $status) {
        $sql = "UPDATE bookings SET status = :status WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $bookingId,
            'status' => $status
        ]);
    }
    
    public function checkExistingBooking($rideId, $passengerId) {
        $sql = "SELECT COUNT(*) FROM bookings 
                WHERE ride_id = :ride_id AND passenger_id = :passenger_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'ride_id' => $rideId,
            'passenger_id' => $passengerId
        ]);
        
        return $stmt->fetchColumn() > 0;
    }
}