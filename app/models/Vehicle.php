<?php

namespace App\Models;
use App\Config\Database;
use PDOException;

class Vehicle {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new vehicle
     * 
     * @param array $data Vehicle data
     * @return bool|int False on failure, vehicle ID on success
     */
    public function create($data) {
        $sql = "INSERT INTO vehicles (user_id, brand, model, year, color, license_plate, eco_friendly, seats) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        try {
            $this->db->query($sql, [
                $data['user_id'],
                $data['brand'],
                $data['model'],
                $data['year'],
                $data['color'] ?? null,
                $data['license_plate'],
                $data['eco_friendly'] ?? false,
                $data['seats']
            ]);
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Get a vehicle by ID
     * 
     * @param int $id Vehicle ID
     * @return array|false Vehicle data or false if not found
     */
    public function getById($id) {
        $sql = "SELECT * FROM vehicles WHERE id = ? LIMIT 1";
        return $this->db->fetch($sql, [$id]);
    }

    /**
     * Get all vehicles belonging to a user
     * 
     * @param int $userId User ID
     * @return array Vehicles
     */
    public function getByUserId($userId) {
        $sql = "SELECT * FROM vehicles WHERE user_id = ? ORDER BY created_at DESC";
        return $this->db->fetchAll($sql, [$userId]);
    }

    /**
     * Update vehicle details
     * 
     * @param int $id Vehicle ID
     * @param array $data Vehicle data
     * @return bool
     */
    public function update($id, $data) {
        $updates = [];
        $params = [];
        
        if (isset($data['brand'])) {
            $updates[] = "brand = ?";
            $params[] = $data['brand'];
        }
        if (isset($data['model'])) {
            $updates[] = "model = ?";
            $params[] = $data['model'];
        }
        if (isset($data['year'])) {
            $updates[] = "year = ?";
            $params[] = $data['year'];
        }
        if (isset($data['color'])) {
            $updates[] = "color = ?";
            $params[] = $data['color'];
        }
        if (isset($data['license_plate'])) {
            $updates[] = "license_plate = ?";
            $params[] = $data['license_plate'];
        }
        if (isset($data['eco_friendly'])) {
            $updates[] = "eco_friendly = ?";
            $params[] = $data['eco_friendly'];
        }
        if (isset($data['seats'])) {
            $updates[] = "seats = ?";
            $params[] = $data['seats'];
        }
        
        if (empty($updates)) return true; // No updates to perform
        
        $sql = "UPDATE vehicles SET " . implode(", ", $updates) . " WHERE id = ?";
        $params[] = $id;
        
        try {
            return $this->db->query($sql, $params);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Get total number of vehicles
     * 
     * @return int
     */
    public function getTotalVehicles() {
        $sql = "SELECT COUNT(*) as total FROM vehicles";
        $result = $this->db->fetch($sql);
        return $result ? (int)$result['total'] : 0;
    }

    /**
     * Get all vehicles with owner details
     * 
     * @return array
     */
    public function getAllVehiclesWithOwners() {
        $sql = "SELECT v.*, 
                    u.first_name AS owner_first_name, 
                    u.last_name AS owner_last_name,
                    u.email AS owner_email
                FROM vehicles v
                JOIN users u ON v.user_id = u.id
                ORDER BY v.created_at DESC";
        
        return $this->db->fetchAll($sql);
    }

    /**
     * Delete a vehicle
     * 
     * @param int $vehicleId
     * @return bool
     */
    public function deleteVehicle($vehicleId) {
        try {
            $sql = "DELETE FROM vehicles WHERE id = ?";
            return $this->db->query($sql, [$vehicleId]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Toggle eco-friendly status
     * 
     * @param int $vehicleId
     * @return bool
     */
    public function toggleEcoFriendly($vehicleId) {
        try {
            $sql = "UPDATE vehicles SET eco_friendly = NOT eco_friendly WHERE id = ?";
            return $this->db->query($sql, [$vehicleId]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function getAll() {
        $sql = "SELECT * FROM vehicles ORDER BY created_at DESC";
        return $this->db->fetchAll($sql);
    }

    public function validate($data) {
        $errors = [];
        
        if (empty($data['brand'])) {
            $errors[] = 'La marque du véhicule est requise';
        }
        
        if (empty($data['model'])) {
            $errors[] = 'Le modèle du véhicule est requis';
        }
        
        if (empty($data['year'])) {
            $errors[] = "L'année du véhicule est requise";
        } elseif (!is_numeric($data['year']) || $data['year'] < 1900 || $data['year'] > date('Y') + 1) {
            $errors[] = "L'année du véhicule n'est pas valide";
        }
        
        return $errors;
    }
}