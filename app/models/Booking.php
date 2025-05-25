<?php

namespace App\Models;

use App\Config\Database;
use PDOException;

class Booking {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Check if a user already has a booking for a specific ride
     * 
     * @param int $rideId Ride ID
     * @param int $passengerId Passenger ID
     * @return bool True if booking exists, false otherwise
     */
    public function checkExistingBooking($rideId, $passengerId) {
        $sql = "SELECT COUNT(*) as count FROM bookings 
                WHERE ride_id = ? AND passenger_id = ? 
                AND status IN ('pending', 'confirmed')";
        
        try {
            $result = $this->db->fetch($sql, [$rideId, $passengerId]);
            return $result && (int)$result['count'] > 0;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Create a new booking
     * 
     * @param array $data Booking data
     * @return bool|int False on failure, booking ID on success
     */
    public function create($data) {
        try {
            $sql = "INSERT INTO bookings (passenger_id, ride_id, seats_booked, status) VALUES (?, ?, ?, 'pending')";
            $this->db->query($sql, [
                $data['user_id'] ?? $data['passenger_id'], 
                $data['ride_id'], 
                $data['seats'] ?? $data['seats_booked'] ?? 1
            ]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Get a booking by ID
     * 
     * @param int $id Booking ID
     * @return array|false Booking data or false if not found
     */
    public function getById($id) {
        $sql = "SELECT * FROM bookings WHERE id = ? LIMIT 1";
        return $this->db->fetch($sql, [$id]);
    }
    
    /**
     * Update booking status
     * 
     * @param int $bookingId Booking ID
     * @param string $status New status
     * @return bool Success status
     */
    public function updateStatus($bookingId, $status) {
        if (!in_array($status, ['pending', 'confirmed', 'rejected', 'cancelled', 'completed'])) {
            return false;
        }
        
        try {
            $sql = "UPDATE bookings SET status = ?, updated_at = NOW() WHERE id = ?";
            return $this->db->query($sql, [$status, $bookingId]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Get bookings for a passenger
     * 
     * @param int $passengerId Passenger ID
     * @return array Bookings
     */
    public function getPassengerBookings($passengerId) {
        $sql = "SELECT b.*, r.departure_location, r.arrival_location, 
                    r.departure_time, r.price, r.status as ride_status,
                    u.first_name as driver_first_name, u.last_name as driver_last_name
                FROM bookings b
                JOIN rides r ON b.ride_id = r.id
                JOIN users u ON r.driver_id = u.id
                WHERE b.passenger_id = ?
                ORDER BY r.departure_time DESC";
        
        return $this->db->fetchAll($sql, [$passengerId]);
    }
    
    /**
     * Get bookings for a passenger (alias for getPassengerBookings)
     * 
     * @param int $passengerId Passenger ID
     * @return array Bookings
     */
    public function getByPassengerId($passengerId) {
        return $this->getPassengerBookings($passengerId);
    }
    
    /**
     * Get bookings for rides where the user is the driver
     * 
     * @param int $driverId Driver ID
     * @return array Bookings
     */
    public function getByDriverId($driverId) {
        $sql = "SELECT b.*, r.departure_location, r.arrival_location, 
                    r.departure_time, r.price, r.status as ride_status,
                    u.first_name as passenger_first_name, u.last_name as passenger_last_name,
                    u.profile_image as passenger_profile_image
                FROM bookings b
                JOIN rides r ON b.ride_id = r.id
                JOIN users u ON b.passenger_id = u.id
                WHERE r.driver_id = ?
                ORDER BY r.departure_time DESC, b.status ASC";
        
        return $this->db->fetchAll($sql, [$driverId]);
    }
    
    /**
     * Get total number of bookings
     * 
     * @return int
     */
    public function getTotalBookings() {
        $sql = "SELECT COUNT(*) as total FROM bookings";
        $result = $this->db->fetch($sql);
        return $result ? (int)$result['total'] : 0;
    }
    
    /**
     * Update available seats in a ride after booking changes
     * 
     * @param int $rideId Ride ID
     * @return bool
     */
    private function updateRideAvailableSeats($rideId) {
        try {
            // Get the original total seats for the ride
            $ride = (new Ride())->getById($rideId);
            
            if (!$ride) {
                return false;
            }
            
            // Calculate total booked seats for confirmed bookings
            $sql = "SELECT SUM(seats_booked) as total_booked 
                    FROM bookings 
                    WHERE ride_id = ? AND status IN ('pending', 'confirmed')";
            
            $result = $this->db->fetch($sql, [$rideId]);
            $totalBooked = $result ? (int)$result['total_booked'] : 0;
            
            // Get the vehicle's total seats
            $vehicle = (new Vehicle())->getById($ride['vehicle_id']);
            $totalSeats = $vehicle ? $vehicle['seats'] : 0;
            
            // Calculate available seats
            $availableSeats = max(0, $totalSeats - $totalBooked);
            
            // Update the ride
            $sql = "UPDATE rides SET available_seats = ? WHERE id = ?";
            return $this->db->query($sql, [$availableSeats, $rideId]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Cancel a booking
     * 
     * @param int $bookingId Booking ID
     * @param string $cancelledBy Who cancelled the booking ('passenger', 'driver', or 'admin')
     * @return bool Success status
     */
    public function cancelBooking($bookingId, $cancelledBy = 'passenger') {
        try {
            $sql = "UPDATE bookings SET status = 'cancelled', cancelled_by = ?, updated_at = NOW() WHERE id = ?";
            return $this->db->query($sql, [$cancelledBy, $bookingId]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete booking
     * 
     * @param int $bookingId Booking ID
     * @return bool Success status
     */
    public function delete($bookingId) {
        try {
            $sql = "DELETE FROM bookings WHERE id = ?";
            return $this->db->query($sql, [$bookingId]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}