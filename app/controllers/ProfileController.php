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
        $bookings = $bookingModel->getPassengerBookings($userId);
        
        // Get credit transactions and requests
        require_once BASE_PATH . '/app/models/CreditTransaction.php';
        require_once BASE_PATH . '/app/models/CreditRequest.php';
        
        $transactionModel = new \App\Models\CreditTransaction();
        $requestModel = new \App\Models\CreditRequest();
        
        // Get recent transactions
        $transactions = $transactionModel->getByUserId($userId, null, 10);
        
        // Get credit requests
        $creditRequests = $requestModel->getByUserId($userId);
        
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
            
            // Capture any output before attempting redirect
            ob_start();
            
            // Update profile
            $updateResult = $this->userModel->updateProfile($userId, $updateData);
            
            // Clean any output that might have been generated
            $output = ob_get_clean();
            
            // Log any output for debugging
            if (!empty($output)) {
                error_log("Profile update output: " . $output);
            }
            
            if ($updateResult) {
                // Set session success message
                $_SESSION['profile_success'] = 'Profil mis à jour avec succès';
                
                // Redirect to profile page after successful update
                header('Location: /profil');
                exit;
            } else {
                // Set session error message
                $_SESSION['profile_error'] = 'Erreur lors de la mise à jour du profil. Veuillez réessayer.';
                
                // Redirect back to profile
                header('Location: /profil');
                exit;
            }
        }
        
        // If not POST request, redirect back to profile
        header('Location: /profil');
        exit;
    }

    /**
     * Show user's credit page
     */
    public function credit() {
        // Check if user is logged in
        \App\Helpers\Auth::requireLogin();
        
        // Set page title and current page
        $pageTitle = 'EcoRide - Mes crédits';
        $currentPage = 'profile-credit';
        
        // Get current user
        $user = \App\Helpers\Auth::user();
        
        // Get full user profile with credit information
        $user = $this->userModel->getById($user['id']);
        
        // Load required models
        require_once BASE_PATH . '/app/models/CreditTransaction.php';
        require_once BASE_PATH . '/app/models/CreditRequest.php';
        
        $transactionModel = new \App\Models\CreditTransaction();
        $requestModel = new \App\Models\CreditRequest();
        
        // Get recent transactions
        $transactions = $transactionModel->getByUserId($user['id'], null, 10);
        
        // Get credit requests
        $creditRequests = $requestModel->getByUserId($user['id']);
        
        // Start output buffering
        ob_start();
        
        // Include the view
        require_once BASE_PATH . '/app/views/profile/credit.php';
        
        // Get the buffered content and clean the buffer
        $content = ob_get_clean();
        
        // Include the layout template
        require_once BASE_PATH . '/app/views/layouts/main.php';
    }

    /**
     * Process credit request
     */
    public function requestCredit() {
        // Check if user is logged in
        \App\Helpers\Auth::requireLogin();
        
        // Set page title and current page
        $pageTitle = 'EcoRide - Demande de crédit';
        $currentPage = 'profile-credit';
        
        // Get current user
        $user = \App\Helpers\Auth::user();
        
        // Get full user profile with credit information
        $user = $this->userModel->getById($user['id']);
        
        // Load required models
        require_once BASE_PATH . '/app/models/CreditRequest.php';
        $requestModel = new \App\Models\CreditRequest();
        
        $errors = [];
        $success = null;
        
        // Process form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get form data
            $amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0;
            $reason = $_POST['reason'] ?? '';
            
            // Validate amount
            if ($amount <= 0) {
                $errors[] = 'Le montant doit être supérieur à 0';
            }
            
            if ($amount < 10) {
                $errors[] = 'Le montant minimum est de 10€';
            }
            
            if ($amount > 1000) {
                $errors[] = 'Le montant maximum est de 1000€';
            }
            
            // Check if user has pending requests
            $pendingRequests = $requestModel->getAll('pending', $user['id']);
            if (!empty($pendingRequests)) {
                $errors[] = 'Vous avez déjà une demande de crédit en attente';
            }
            
            if (empty($errors)) {
                // Create credit request
                $requestId = $requestModel->create($user['id'], $amount, $reason);
                
                if ($requestId) {
                    $success = 'Votre demande de crédit a été envoyée avec succès. Un administrateur l\'examinera prochainement.';
                } else {
                    $errors[] = 'Une erreur est survenue lors de la création de la demande';
                }
            }
        }
        
        // Load transactions and requests for the view
        require_once BASE_PATH . '/app/models/CreditTransaction.php';
        $transactionModel = new \App\Models\CreditTransaction();
        
        // Get recent transactions
        $transactions = $transactionModel->getByUserId($user['id'], null, 10);
        
        // Get credit requests
        $creditRequests = $requestModel->getByUserId($user['id']);
        
        // Start output buffering
        ob_start();
        
        // Include the view
        require_once BASE_PATH . '/app/views/profile/credit.php';
        
        // Get the buffered content and clean the buffer
        $content = ob_get_clean();
        
        // Include the layout template
        require_once BASE_PATH . '/app/views/layouts/main.php';
    }
}