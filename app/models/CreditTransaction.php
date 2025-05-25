<?php

namespace App\Models;

use App\Config\Database;
use PDOException;

class CreditTransaction {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Create a new transaction
     * 
     * @param int $userId User ID
     * @param float $amount Transaction amount
     * @param string $type Transaction type
     * @param int|null $referenceId Reference ID (booking ID, ride ID, etc.)
     * @param string $description Transaction description
     * @param int|null $relatedUserId Related user ID
     * @return bool|int Transaction ID on success, false on failure
     */
    public function create($userId, $amount, $type, $referenceId = null, $description = '', $relatedUserId = null) {
        if (!in_array($type, ['booking', 'cancellation', 'ride_earnings', 'admin_adjustment', 'credit_request'])) {
            return false;
        }
        
        try {
            $sql = "INSERT INTO credit_transactions 
                    (user_id, amount, type, reference_id, description, related_user_id, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";
            
            $this->db->query($sql, [
                $userId,
                $amount,
                $type,
                $referenceId,
                $description,
                $relatedUserId
            ]);
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Get transactions for a specific user
     * 
     * @param int $userId User ID
     * @param string|null $type Filter by transaction type
     * @param int $limit Limit number of results
     * @param int $offset Offset for pagination
     * @return array Array of transactions
     */
    public function getByUserId($userId, $type = null, $limit = 50, $offset = 0) {
        $params = [$userId];
        $whereClause = "WHERE user_id = ?";
        
        if ($type) {
            $whereClause .= " AND type = ?";
            $params[] = $type;
        }
        
        $sql = "SELECT ct.*, 
                    u.first_name as related_user_first_name, 
                    u.last_name as related_user_last_name
                FROM credit_transactions ct
                LEFT JOIN users u ON ct.related_user_id = u.id
                $whereClause
                ORDER BY ct.created_at DESC
                LIMIT $limit OFFSET $offset";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get transactions related to a specific reference (booking, ride, etc.)
     * 
     * @param string $type Transaction type
     * @param int $referenceId Reference ID
     * @return array Array of transactions
     */
    public function getByReference($type, $referenceId) {
        $sql = "SELECT ct.*, 
                    u.first_name as user_first_name, 
                    u.last_name as user_last_name,
                    r.first_name as related_user_first_name, 
                    r.last_name as related_user_last_name
                FROM credit_transactions ct
                JOIN users u ON ct.user_id = u.id
                LEFT JOIN users r ON ct.related_user_id = r.id
                WHERE ct.type = ? AND ct.reference_id = ?
                ORDER BY ct.created_at DESC";
        
        return $this->db->fetchAll($sql, [$type, $referenceId]);
    }
    
    /**
     * Get all transactions with filtering and pagination
     * 
     * @param string|null $type Filter by transaction type
     * @param int|null $userId Filter by user ID
     * @param int $limit Limit number of results
     * @param int $offset Offset for pagination
     * @return array Array of transactions
     */
    public function getAll($type = null, $userId = null, $limit = 50, $offset = 0) {
        $params = [];
        $whereClause = [];
        
        if ($type) {
            $whereClause[] = "ct.type = ?";
            $params[] = $type;
        }
        
        if ($userId) {
            $whereClause[] = "ct.user_id = ?";
            $params[] = $userId;
        }
        
        $whereStr = empty($whereClause) ? "" : "WHERE " . implode(" AND ", $whereClause);
        
        $sql = "SELECT ct.*, 
                    u.first_name as user_first_name, 
                    u.last_name as user_last_name,
                    r.first_name as related_user_first_name, 
                    r.last_name as related_user_last_name
                FROM credit_transactions ct
                JOIN users u ON ct.user_id = u.id
                LEFT JOIN users r ON ct.related_user_id = r.id
                $whereStr
                ORDER BY ct.created_at DESC
                LIMIT $limit OFFSET $offset";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get total transactions count with filters
     * 
     * @param string|null $type Filter by transaction type
     * @param int|null $userId Filter by user ID
     * @return int Total count
     */
    public function getTotalCount($type = null, $userId = null) {
        $params = [];
        $whereClause = [];
        
        if ($type) {
            $whereClause[] = "type = ?";
            $params[] = $type;
        }
        
        if ($userId) {
            $whereClause[] = "user_id = ?";
            $params[] = $userId;
        }
        
        $whereStr = empty($whereClause) ? "" : "WHERE " . implode(" AND ", $whereClause);
        
        $sql = "SELECT COUNT(*) as total FROM credit_transactions $whereStr";
        $result = $this->db->fetch($sql, $params);
        
        return $result ? (int)$result['total'] : 0;
    }
} 