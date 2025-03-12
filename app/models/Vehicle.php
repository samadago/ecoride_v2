<?php

namespace App\Models;
use App\Config\Database;

class Vehicle {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($data) {
        $sql = "INSERT INTO vehicles (user_id, brand, model, year, eco_friendly, created_at, updated_at) 
                VALUES (:user_id, :brand, :model, :year, :eco_friendly, NOW(), NOW())";
        
        return $this->db->query($sql, [
            'user_id' => $data['user_id'],
            'brand' => $data['brand'],
            'model' => $data['model'],
            'year' => $data['year'],
            'eco_friendly' => isset($data['eco_friendly']) ? 1 : 0
        ]);
    }

    public function update($id, $data) {
        $sql = "UPDATE vehicles 
                SET brand = :brand, model = :model, year = :year, 
                    eco_friendly = :eco_friendly, updated_at = NOW() 
                WHERE id = :id AND user_id = :user_id";
        
        return $this->db->query($sql, [
            'id' => $id,
            'user_id' => $data['user_id'],
            'brand' => $data['brand'],
            'model' => $data['model'],
            'year' => $data['year'],
            'eco_friendly' => isset($data['eco_friendly']) ? 1 : 0
        ]);
    }

    public function delete($id, $user_id) {
        $sql = "DELETE FROM vehicles WHERE id = :id AND user_id = :user_id";
        return $this->db->query($sql, ['id' => $id, 'user_id' => $user_id]);
    }

    public function findById($id) {
        $sql = "SELECT * FROM vehicles WHERE id = :id";
        return $this->db->fetch($sql, ['id' => $id]);
    }

    public function getByUserId($user_id) {
        $sql = "SELECT * FROM vehicles WHERE user_id = :user_id ORDER BY created_at DESC";
        return $this->db->fetchAll($sql, ['user_id' => $user_id]);
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