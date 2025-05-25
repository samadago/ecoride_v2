<?php

namespace App\Models;

use App\Config\Database;
use PDOException;

class CreditRequest {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Create a new credit request
     * 
     * @param int $userId User ID
     * @param float $amount Amount requested
     * @param string $reason Reason for the request
     * @return bool|int ID of the created request or false on failure
     */
    public function create($userId, $amount, $reason = '') {
        try {
            $sql = "INSERT INTO credit_requests (user_id, amount, reason) VALUES (?, ?, ?)";
            $this->db->query($sql, [$userId, $amount, $reason]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Get a credit request by ID
     * 
     * @param int $id Request ID
     * @return array|false Request data or false if not found
     */
    public function getById($id) {
        $sql = "SELECT cr.*, 
                    u.first_name as user_first_name, 
                    u.last_name as user_last_name,
                    a.first_name as admin_first_name, 
                    a.last_name as admin_last_name
                FROM credit_requests cr
                JOIN users u ON cr.user_id = u.id
                LEFT JOIN users a ON cr.admin_id = a.id
                WHERE cr.id = ?";
        return $this->db->fetch($sql, [$id]);
    }
    
    /**
     * Get all credit requests with optional filters
     * 
     * @param string $status Status filter (null for all)
     * @param int $userId User ID filter (null for all)
     * @return array Array of credit requests
     */
    public function getAll($status = null, $userId = null) {
        $params = [];
        $whereClause = [];
        
        if ($status) {
            $whereClause[] = "cr.status = ?";
            $params[] = $status;
        }
        
        if ($userId) {
            $whereClause[] = "cr.user_id = ?";
            $params[] = $userId;
        }
        
        $whereStr = empty($whereClause) ? "" : "WHERE " . implode(" AND ", $whereClause);
        
        $sql = "SELECT cr.*, 
                    u.first_name as user_first_name, 
                    u.last_name as user_last_name,
                    a.first_name as admin_first_name, 
                    a.last_name as admin_last_name
                FROM credit_requests cr
                JOIN users u ON cr.user_id = u.id
                LEFT JOIN users a ON cr.admin_id = a.id
                $whereStr
                ORDER BY cr.created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get credit requests for a specific user
     * 
     * @param int $userId User ID
     * @return array Array of credit requests
     */
    public function getByUserId($userId) {
        return $this->getAll(null, $userId);
    }
    
    /**
     * Process a credit request (approve or reject)
     * 
     * @param int $requestId Request ID
     * @param string $status New status ('approved' or 'rejected')
     * @param int $adminId Admin ID who processed the request
     * @return bool Success status
     */
    public function processRequest($requestId, $status, $adminId) {
        if (!in_array($status, ['approved', 'rejected'])) {
            return false;
        }
        
        // Check if we're already in a transaction
        $hasActiveTransaction = $this->db->getConnection()->inTransaction();
        
        try {
            // Only start a transaction if one isn't already active
            if (!$hasActiveTransaction) {
                $this->db->beginTransaction();
            }
            
            // Update the request
            $sql = "UPDATE credit_requests SET status = ?, admin_id = ?, updated_at = NOW() WHERE id = ?";
            $result = $this->db->query($sql, [$status, $adminId, $requestId]);
            
            if (!$result) {
                if (!$hasActiveTransaction) {
                    $this->db->rollBack();
                }
                return false;
            }
            
            // If approved, add credits to user
            if ($status === 'approved') {
                $request = $this->getById($requestId);
                
                if (!$request) {
                    if (!$hasActiveTransaction) {
                        $this->db->rollBack();
                    }
                    return false;
                }
                
                // Update user's balance
                $userModel = new User();
                $success = $userModel->adjustCredit(
                    $request['user_id'], 
                    $request['amount'], 
                    'credit_request', 
                    $requestId,
                    "Credit request approved by admin"
                );
                
                if (!$success) {
                    if (!$hasActiveTransaction) {
                        $this->db->rollBack();
                    }
                    return false;
                }
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