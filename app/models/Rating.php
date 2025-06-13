<?php

namespace App\Models;

use App\Config\Database;
use PDOException;

class Rating {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Create a new rating/review
     * 
     * @param int $bookingId Booking ID
     * @param int $fromUserId User giving the rating
     * @param int $toUserId User receiving the rating
     * @param int $rating Rating value (1-5)
     * @param string $comment Optional comment
     * @return bool|int Rating ID on success, false on failure
     */
    public function create($bookingId, $fromUserId, $toUserId, $rating, $comment = '') {
        // Validate rating
        if ($rating < 1 || $rating > 5) {
            return false;
        }
        
        // Check if rating already exists
        if ($this->hasRated($fromUserId, $toUserId, $bookingId)) {
            return false;
        }
        
        // Auto-approve ratings without comments, require approval for ratings with comments
        $status = empty(trim($comment)) ? 'approved' : 'pending';
        
        try {
            $sql = "INSERT INTO ratings (booking_id, from_user_id, to_user_id, rating, comment, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";
            
            $this->db->query($sql, [$bookingId, $fromUserId, $toUserId, $rating, $comment, $status]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if user has already rated another user for a specific booking
     * 
     * @param int $fromUserId User giving the rating
     * @param int $toUserId User receiving the rating
     * @param int $bookingId Booking ID
     * @return bool
     */
    public function hasRated($fromUserId, $toUserId, $bookingId) {
        $sql = "SELECT COUNT(*) as count FROM ratings 
                WHERE from_user_id = ? AND to_user_id = ? AND booking_id = ?";
        
        $result = $this->db->fetch($sql, [$fromUserId, $toUserId, $bookingId]);
        return $result && $result['count'] > 0;
    }
    
    /**
     * Get ratings for a specific user (approved only)
     * 
     * @param int $userId User ID
     * @param int $limit Limit results
     * @param int $offset Offset for pagination
     * @return array
     */
    public function getUserRatings($userId, $limit = 10, $offset = 0) {
        $limit = (int)$limit;
        $offset = (int)$offset;
        
        $sql = "SELECT r.*, 
                    u.first_name as from_user_first_name, 
                    u.last_name as from_user_last_name,
                    b.ride_id,
                    CASE 
                        WHEN r.comment = '' OR r.comment IS NULL THEN 1
                        WHEN r.status = 'approved' THEN 1
                        ELSE 0
                    END as comment_approved
                FROM ratings r
                JOIN users u ON r.from_user_id = u.id
                JOIN bookings b ON r.booking_id = b.id
                WHERE r.to_user_id = ? AND r.status = 'approved'
                ORDER BY r.created_at DESC
                LIMIT $limit OFFSET $offset";
        
        return $this->db->fetchAll($sql, [$userId]);
    }
    
    /**
     * Get user's average rating and count (approved only)
     * 
     * @param int $userId User ID
     * @return array
     */
    public function getUserRatingSummary($userId) {
        $sql = "SELECT 
                COALESCE(AVG(rating), 0) AS average_rating, 
                COUNT(*) as rating_count
            FROM ratings
            WHERE to_user_id = ? AND status = 'approved'";
            
        return $this->db->fetch($sql, [$userId]);
    }
    
    /**
     * Get ratings pending moderation (only those with comments)
     * 
     * @param int $limit Limit results
     * @param int $offset Offset for pagination
     * @return array
     */
    public function getPendingRatings($limit = 50, $offset = 0) {
        $limit = (int)$limit;
        $offset = (int)$offset;
        
        $sql = "SELECT r.*, 
                    f.first_name as from_user_first_name, 
                    f.last_name as from_user_last_name,
                    t.first_name as to_user_first_name, 
                    t.last_name as to_user_last_name,
                    b.ride_id,
                    ride.departure_location,
                    ride.arrival_location
                FROM ratings r
                JOIN users f ON r.from_user_id = f.id
                JOIN users t ON r.to_user_id = t.id
                JOIN bookings b ON r.booking_id = b.id
                JOIN rides ride ON b.ride_id = ride.id
                WHERE r.status = 'pending' AND r.comment IS NOT NULL AND r.comment != ''
                ORDER BY r.created_at ASC
                LIMIT $limit OFFSET $offset";
        
        return $this->db->fetchAll($sql, []);
    }
    
    /**
     * Moderate a rating (approve/reject)
     * 
     * @param int $ratingId Rating ID
     * @param string $status New status ('approved', 'rejected')
     * @param int $adminId Admin ID
     * @param string $notes Admin notes
     * @return bool
     */
    public function moderate($ratingId, $status, $adminId, $notes = '') {
        if (!in_array($status, ['approved', 'rejected'])) {
            return false;
        }
        
        try {
            $sql = "UPDATE ratings 
                    SET status = ?, admin_id = ?, moderated_at = NOW(), admin_notes = ?
                    WHERE id = ?";
            
            return $this->db->query($sql, [$status, $adminId, $notes, $ratingId]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Get rating by ID
     * 
     * @param int $ratingId Rating ID
     * @return array|false
     */
    public function getById($ratingId) {
        $sql = "SELECT r.*, 
                    f.first_name as from_user_first_name, 
                    f.last_name as from_user_last_name,
                    t.first_name as to_user_first_name, 
                    t.last_name as to_user_last_name,
                    a.first_name as admin_first_name,
                    a.last_name as admin_last_name,
                    b.ride_id
                FROM ratings r
                JOIN users f ON r.from_user_id = f.id
                JOIN users t ON r.to_user_id = t.id
                LEFT JOIN users a ON r.admin_id = a.id
                JOIN bookings b ON r.booking_id = b.id
                WHERE r.id = ?";
        
        return $this->db->fetch($sql, [$ratingId]);
    }
    
    /**
     * Get ratings for a specific ride (approved only)
     * 
     * @param int $rideId Ride ID
     * @return array
     */
    public function getRideRatings($rideId) {
        $sql = "SELECT r.*, 
                    f.first_name as from_user_first_name, 
                    f.last_name as from_user_last_name,
                    t.first_name as to_user_first_name, 
                    t.last_name as to_user_last_name
                FROM ratings r
                JOIN users f ON r.from_user_id = f.id
                JOIN users t ON r.to_user_id = t.id
                JOIN bookings b ON r.booking_id = b.id
                WHERE b.ride_id = ? AND r.status = 'approved'
                ORDER BY r.created_at DESC";
        
        return $this->db->fetchAll($sql, [$rideId]);
    }
    
    /**
     * Get count of pending ratings (only those with comments)
     * 
     * @return int
     */
    public function getPendingCount() {
        $sql = "SELECT COUNT(*) as count FROM ratings WHERE status = 'pending' AND comment IS NOT NULL AND comment != ''";
        $result = $this->db->fetch($sql);
        return $result ? (int)$result['count'] : 0;
    }
    
    /**
     * Delete a rating
     * 
     * @param int $ratingId Rating ID
     * @return bool
     */
    public function delete($ratingId) {
        try {
            $sql = "DELETE FROM ratings WHERE id = ?";
            return $this->db->query($sql, [$ratingId]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
} 