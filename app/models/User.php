<?php

namespace App\Models;

use App\Config\Database;
use PDOException;

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Register a new user
     * 
     * @param array $data User data
     * @return bool|int False on failure, user ID on success
     */
    public function register($data) {
        $sql = "INSERT INTO users (first_name, last_name, email, password_hash, profile_image, phone, bio, is_admin, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, FALSE, NOW())";
        
        // Use the password_hash from the data if provided, otherwise hash the password
        $hashedPassword = $data['password_hash'] ?? password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Handle profile image upload
        $profileImage = null;
        if (isset($data['profile_image']) && $data['profile_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/assets/uploads/';
            $fileExtension = strtolower(pathinfo($data['profile_image']['name'], PATHINFO_EXTENSION));
            $newFileName = uniqid('profile_') . '.' . $fileExtension;
            $uploadFile = $uploadDir . $newFileName;
            
            if (move_uploaded_file($data['profile_image']['tmp_name'], $uploadFile)) {
                $profileImage = 'assets/uploads/' . $newFileName;
            }
        }
        
        try {
            $this->db->query($sql, [
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $hashedPassword,
                $profileImage,
                $data['phone'] ?? null,
                $data['bio'] ?? null
            ]);

            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            // Check for duplicate email
            if ($e->getCode() == 23000) {
                log($e->getMessage());
                return false;
            }
            throw $e;
        }
    }
    
    /**
     * Find user by email
     * 
     * @param string $email User email
     * @return array|false User data or false if not found
     */
    public function findByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = ? LIMIT 1";
        return $this->db->fetch($sql, [$email]);
    }
    
    /**
     * Find user by ID
     * 
     * @param int $id User ID
     * @return array|false User data or false if not found
     */
    public function getById($id) {
        $sql = "SELECT * FROM users WHERE id = ? LIMIT 1";
        return $this->db->fetch($sql, [$id]);
    }
    
    /**
     * Verify password
     * 
     * @param string $password Plain password
     * @param string $hashedPassword Hashed password
     * @return bool
     */
    public function verifyPassword($password, $hashedPassword) {
        return password_verify($password, $hashedPassword);
    }
    
    /**
     * Update user profile
     * 
     * @param int $id User ID
     * @param array $data User data to update
     * @return bool
     */
    public function updateProfile($id, $data) {
        $currentUser = $this->getById($id);
        if (!$currentUser) return false;

        $updates = [];
        $params = [];

        // Handle profile image upload
        if (isset($data['profile_image']) && $data['profile_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/assets/uploads/';
            $fileExtension = strtolower(pathinfo($data['profile_image']['name'], PATHINFO_EXTENSION));
            $newFileName = uniqid('profile_') . '.' . $fileExtension;
            $uploadFile = $uploadDir . $newFileName;
            
            if (move_uploaded_file($data['profile_image']['tmp_name'], $uploadFile)) {
                $updates[] = "profile_image = ?";
                $params[] = 'assets/uploads/' . $newFileName;
                
                // Delete old profile image if exists
                if ($currentUser['profile_image']) {
                    $oldFile = $_SERVER['DOCUMENT_ROOT'] . '/' . $currentUser['profile_image'];
                    if (file_exists($oldFile)) unlink($oldFile);
                }
            }
        }

        // Update other fields
        if (!empty($data['first_name'])) {
            $updates[] = "first_name = ?";
            $params[] = $data['first_name'];
        }
        if (!empty($data['last_name'])) {
            $updates[] = "last_name = ?";
            $params[] = $data['last_name'];
        }
        if (!empty($data['email'])) {
            $updates[] = "email = ?";
            $params[] = $data['email'];
        }
        if (!empty($data['phone'])) {
            $updates[] = "phone = ?";
            $params[] = $data['phone'];
        }
        if (isset($data['bio'])) {
            $updates[] = "bio = ?";
            $params[] = $data['bio'];
        }
        if (!empty($data['password'])) {
            $updates[] = "password_hash = ?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        if (empty($updates)) return true; // No updates to perform

        $updates[] = "updated_at = NOW()";
        $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
        $params[] = $id;
        
        try {
            return $this->db->query($sql, $params);
        } catch (PDOException $e) {
            log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Change user password
     * 
     * @param int $id User ID
     * @param string $newPassword New password
     * @return bool
     */
    public function changePassword($id, $newPassword) {
        $sql = "UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?";
        
        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        try {
            $this->db->query($sql, [$hashedPassword, $id]);
            return true;
        } catch (PDOException $e) {
            log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user's rating
     * 
     * @param int $userId User ID
     * @return array Rating data including average and count
     */
    public function getUserRating($userId) {
        $sql = "SELECT 
                COALESCE(AVG(rating), 0) AS average_rating, 
                COUNT(*) as rating_count
            FROM ratings
            WHERE to_user_id = :user_id";
            
        return $this->db->fetch($sql, ['user_id' => $userId]);
    }
}