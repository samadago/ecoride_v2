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
            // Use the correct path for Docker environment
            $uploadDir = dirname($_SERVER['DOCUMENT_ROOT']) . '/public/assets/uploads/';
            
            // Create upload directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    error_log("Failed to create upload directory: " . $uploadDir);
                    return false;
                }
            }
            
            // Check if directory is writable
            if (!is_writable($uploadDir)) {
                error_log("Upload directory is not writable: " . $uploadDir);
                return false;
            }
            
            $fileExtension = strtolower(pathinfo($data['profile_image']['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            // Validate file extension
            if (!in_array($fileExtension, $allowedExtensions)) {
                error_log("Invalid file extension: " . $fileExtension);
                return false;
            }
            
            $newFileName = uniqid('profile_') . '.' . $fileExtension;
            $uploadFile = $uploadDir . $newFileName;
            
            if (move_uploaded_file($data['profile_image']['tmp_name'], $uploadFile)) {
                // Set proper file permissions
                chmod($uploadFile, 0644);
                $profileImage = 'assets/uploads/' . $newFileName;
            } else {
                error_log("Failed to move uploaded file to: " . $uploadFile);
                return false;
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
                error_log($e->getMessage());
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
            // Use the correct path for Docker environment
            $uploadDir = dirname($_SERVER['DOCUMENT_ROOT']) . '/public/assets/uploads/';
            
            // Create upload directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    error_log("Failed to create upload directory: " . $uploadDir);
                    return false;
                }
            }
            
            // Check if directory is writable
            if (!is_writable($uploadDir)) {
                error_log("Upload directory is not writable: " . $uploadDir);
                return false;
            }
            
            $fileExtension = strtolower(pathinfo($data['profile_image']['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            // Validate file extension
            if (!in_array($fileExtension, $allowedExtensions)) {
                error_log("Invalid file extension: " . $fileExtension);
                return false;
            }
            
            $newFileName = uniqid('profile_') . '.' . $fileExtension;
            $uploadFile = $uploadDir . $newFileName;
            
            if (move_uploaded_file($data['profile_image']['tmp_name'], $uploadFile)) {
                // Set proper file permissions
                chmod($uploadFile, 0644);
                
                $updates[] = "profile_image = ?";
                $params[] = 'assets/uploads/' . $newFileName;
                
                // Delete old profile image if exists
                if ($currentUser['profile_image']) {
                    $oldFile = dirname($_SERVER['DOCUMENT_ROOT']) . '/public/' . $currentUser['profile_image'];
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }
            } else {
                error_log("Failed to move uploaded file to: " . $uploadFile);
                return false;
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
            error_log($e->getMessage());
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
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user's rating (only approved ratings)
     * 
     * @param int $userId User ID
     * @return array Rating data including average and count
     */
    public function getUserRating($userId) {
        $sql = "SELECT 
                COALESCE(AVG(rating), 0) AS average_rating, 
                COUNT(*) as rating_count
            FROM ratings
            WHERE to_user_id = :user_id AND status = 'approved'";
            
        return $this->db->fetch($sql, ['user_id' => $userId]);
    }

    /**
     * Get total number of users
     * 
     * @return int
     */
    public function getTotalUsers() {
        $sql = "SELECT COUNT(*) as total FROM users";
        $result = $this->db->fetch($sql);
        return $result ? (int)$result['total'] : 0;
    }

    /**
     * Get all users
     * 
     * @return array
     */
    public function getAllUsers() {
        $sql = "SELECT id, first_name, last_name, email, profile_image, is_admin, created_at, credit FROM users ORDER BY created_at DESC";
        return $this->db->fetchAll($sql);
    }

    /**
     * Delete a user
     * 
     * @param int $userId
     * @return bool
     */
    public function deleteUser($userId) {
        try {
            // First get the user to check if they have a profile image
            $user = $this->getById($userId);
            
            // Delete the user
            $sql = "DELETE FROM users WHERE id = ?";
            $result = $this->db->query($sql, [$userId]);
            
            // If successful and user had a profile image, delete it
            if ($result && $user && !empty($user['profile_image'])) {
                $profileImage = dirname($_SERVER['DOCUMENT_ROOT']) . '/public/' . $user['profile_image'];
                if (file_exists($profileImage)) {
                    unlink($profileImage);
                }
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Toggle admin status for a user
     * 
     * @param int $userId
     * @return bool
     */
    public function toggleAdminStatus($userId) {
        try {
            $sql = "UPDATE users SET is_admin = NOT is_admin WHERE id = ?";
            return $this->db->query($sql, [$userId]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Create a user from admin panel
     * 
     * @param array $data User data including is_admin flag
     * @return bool|int False on failure, user ID on success
     */
    public function createUserFromAdmin($data) {
        $sql = "INSERT INTO users (first_name, last_name, email, password_hash, is_admin, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
        
        // Hash the password
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        try {
            $this->db->query($sql, [
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $hashedPassword,
                $data['is_admin'] ? 1 : 0
            ]);
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            // Check for duplicate email
            if ($e->getCode() == 23000) {
                error_log($e->getMessage());
                return false;
            }
            throw $e;
        }
    }

    /**
     * Get user's credit balance
     * 
     * @param int $userId User ID
     * @return float User's current credit balance
     */
    public function getCredit($userId) {
        $sql = "SELECT credit FROM users WHERE id = ?";
        $result = $this->db->fetch($sql, [$userId]);
        return $result ? (float)$result['credit'] : 0;
    }

    /**
     * Check if user has enough credit
     * 
     * @param int $userId User ID
     * @param float $amount Amount to check
     * @return bool True if user has enough credit
     */
    public function hasEnoughCredit($userId, $amount) {
        $credit = $this->getCredit($userId);
        return $credit >= $amount;
    }

    /**
     * Adjust user's credit balance and create a transaction record
     * 
     * @param int $userId User ID
     * @param float $amount Amount to adjust (positive for add, negative for deduct)
     * @param string $type Transaction type
     * @param int|null $referenceId Reference ID (booking ID, ride ID, etc.)
     * @param string $description Transaction description
     * @param int|null $relatedUserId Related user ID
     * @return bool Success status
     */
    public function adjustCredit($userId, $amount, $type, $referenceId = null, $description = '', $relatedUserId = null) {
        // Make sure amount is a float
        $amount = (float)$amount;
        
        // Check if we're already in a transaction
        $hasActiveTransaction = $this->db->getConnection()->inTransaction();
        
        try {
            // Only start a transaction if one isn't already active
            if (!$hasActiveTransaction) {
                $this->db->beginTransaction();
            }
            
            // Update user's credit
            $sql = "UPDATE users SET credit = credit + ? WHERE id = ?";
            $result = $this->db->query($sql, [$amount, $userId]);
            
            if (!$result) {
                if (!$hasActiveTransaction) {
                    $this->db->rollBack();
                }
                return false;
            }
            
            // Create transaction record
            $transactionModel = new CreditTransaction();
            $transactionId = $transactionModel->create($userId, $amount, $type, $referenceId, $description, $relatedUserId);
            
            if (!$transactionId) {
                if (!$hasActiveTransaction) {
                    $this->db->rollBack();
                }
                return false;
            }
            
            // Only commit if we started the transaction
            if (!$hasActiveTransaction) {
                $this->db->commit();
            }
            
            return true;
        } catch (PDOException $e) {
            // Only rollback if we started the transaction
            if (!$hasActiveTransaction) {
                $this->db->rollBack();
            }
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Transfer credits between users
     * 
     * @param int $fromUserId From user ID
     * @param int $toUserId To user ID
     * @param float $amount Amount to transfer
     * @param string $type Transaction type
     * @param int|null $referenceId Reference ID
     * @param string $description Transaction description
     * @return bool Success status
     */
    public function transferCredit($fromUserId, $toUserId, $amount, $type, $referenceId = null, $description = '') {
        // Validate amount
        if ($amount <= 0) {
            return false;
        }
        
        // Check if from user has enough credit
        if (!$this->hasEnoughCredit($fromUserId, $amount)) {
            return false;
        }
        
        // Check if we're already in a transaction
        $hasActiveTransaction = $this->db->getConnection()->inTransaction();
        
        try {
            // Only start a transaction if one isn't already active
            if (!$hasActiveTransaction) {
                $this->db->beginTransaction();
            }
            
            // Deduct from sender
            $deductResult = $this->adjustCredit($fromUserId, -$amount, $type, $referenceId, $description, $toUserId);
            
            if (!$deductResult) {
                if (!$hasActiveTransaction) {
                    $this->db->rollBack();
                }
                return false;
            }
            
            // Add to receiver
            $addResult = $this->adjustCredit($toUserId, $amount, $type, $referenceId, $description, $fromUserId);
            
            if (!$addResult) {
                if (!$hasActiveTransaction) {
                    $this->db->rollBack();
                }
                return false;
            }
            
            // Only commit if we started the transaction
            if (!$hasActiveTransaction) {
                $this->db->commit();
            }
            
            return true;
        } catch (PDOException $e) {
            // Only rollback if we started the transaction
            if (!$hasActiveTransaction) {
                $this->db->rollBack();
            }
            error_log($e->getMessage());
            return false;
        }
    }
}
