<?php

namespace App\Controllers;

use App\Helpers\Auth;

/**
 * Profile Controller
 * 
 * This controller handles user profile functionality.
 */

class ProfileController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new \App\Models\User();
    }
    
    /**
     * Display user profile
     * 
     * @return void
     */
    public function index() {
        // Check if user is logged in
        Auth::requireLogin();
        
        // Get current user's data
        $user = Auth::user();
        $userId = $user['id'];
        
        // Get user's profile data
        $profile = $this->userModel->getById($userId);
        
        // Get user's rides
        $rideModel = new \App\Models\Ride();
        $userRides = $rideModel->getByUserId($userId);
        
        // Get user's bookings
        $bookingModel = new \App\Models\Booking();
        $bookings = $bookingModel->getByPassengerId($userId);
        
        // Include the view
        require_once BASE_PATH . '/app/views/profile/index.php';
    }

    /**
     * Update user profile
     * 
     * @return void
     */
    public function update() {
        // Check if user is logged in
        Auth::requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = Auth::user();
            $userId = $user['id'];
            
            // Prepare update data
            $updateData = [
                'first_name' => $_POST['first_name'] ?? '',
                'last_name' => $_POST['last_name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'bio' => $_POST['bio'] ?? ''
            ];
            
            // Handle profile image if uploaded
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $updateData['profile_image'] = $_FILES['profile_image'];
            }
            
            // Handle password update if provided
            if (!empty($_POST['password'])) {
                $updateData['password'] = $_POST['password'];
            }
            
            // Update profile
            if ($this->userModel->updateProfile($userId, $updateData)) {
                // Redirect to profile page after successful update
                header('Location: /profil');
                exit;
            }
        }
        
        // If update fails or not POST request, redirect back to profile
        header('Location: /profil');
        exit;
    }
}