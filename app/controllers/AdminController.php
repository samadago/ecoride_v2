<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\Ride;
use App\Models\Vehicle;
use App\Models\Booking;

/**
 * Admin Controller
 * 
 * This controller handles admin panel functionality.
 */
class AdminController {
    private $userModel;
    private $rideModel;
    private $vehicleModel;
    private $bookingModel;
    
    public function __construct() {
        $this->userModel = new User();
        $this->rideModel = new Ride();
        $this->vehicleModel = new Vehicle();
        $this->bookingModel = new Booking();
        
        // Check if user is logged in and is admin
        $this->requireAdmin();
    }
    
    /**
     * Require admin privileges for accessing any method in this controller
     */
    private function requireAdmin() {
        \App\Helpers\Auth::requireLogin('/connexion');
        
        if (!\App\Helpers\Auth::isAdmin()) {
            $_SESSION['error'] = "Accès non autorisé. Vous devez être administrateur pour accéder à cette page.";
            header('Location: /');
            exit;
        }
    }
    
    /**
     * Display admin dashboard with KPIs
     */
    public function dashboard() {
        $pageTitle = 'EcoRide - Administration';
        $currentPage = 'admin';
        
        // Get stats for KPIs
        $stats = [
            'totalUsers' => $this->userModel->getTotalUsers(),
            'totalRides' => $this->rideModel->getTotalRides(),
            'totalVehicles' => $this->vehicleModel->getTotalVehicles(),
            'totalBookings' => $this->bookingModel->getTotalBookings(),
            'pendingRides' => $this->rideModel->countRidesByStatus('pending'),
            'ongoingRides' => $this->rideModel->countRidesByStatus('ongoing'),
            'completedRides' => $this->rideModel->countRidesByStatus('completed'),
            'cancelledRides' => $this->rideModel->countRidesByStatus('cancelled')
        ];
        
        // Start output buffering
        ob_start();
        
        // Include the view
        require_once BASE_PATH . '/app/views/admin/dashboard.php';
        
        // Get the buffered content and clean the buffer
        $content = ob_get_clean();
        
        // Include the admin layout template
        require_once BASE_PATH . '/app/views/layouts/admin.php';
    }
    
    /**
     * List all users with management options
     */
    public function users() {
        $pageTitle = 'EcoRide - Gestion des utilisateurs';
        $currentPage = 'admin-users';
        
        // Get all users
        $users = $this->userModel->getAllUsers();
        
        // Start output buffering
        ob_start();
        
        // Include the view
        require_once BASE_PATH . '/app/views/admin/users.php';
        
        // Get the buffered content and clean the buffer
        $content = ob_get_clean();
        
        // Include the admin layout template
        require_once BASE_PATH . '/app/views/layouts/admin.php';
    }
    
    /**
     * Delete a user
     */
    public function deleteUser($userId) {
        // Prevent deletion of the first admin (ID 1)
        if ($userId == 1) {
            $_SESSION['error'] = "Impossible de supprimer l'administrateur principal.";
            header('Location: /admin/users');
            exit;
        }
        
        if ($this->userModel->deleteUser($userId)) {
            $_SESSION['success'] = "L'utilisateur a été supprimé avec succès.";
        } else {
            $_SESSION['error'] = "Une erreur est survenue lors de la suppression de l'utilisateur.";
        }
        
        // Redirect back to users page
        header('Location: /admin/users');
        exit;
    }
    
    /**
     * Toggle admin status for a user
     */
    public function toggleAdminStatus($userId) {
        // First, check if we're trying to make a user an admin
        $user = $this->userModel->getById($userId);
        
        if (!$user) {
            $_SESSION['error'] = "Utilisateur non trouvé.";
            header('Location: /admin/users');
            exit;
        }
        
        // If user is not an admin and we're trying to make them admin, check email
        if (!$user['is_admin']) {
            // Extract domain part (after @) from email
            $emailParts = explode('@', $user['email']);
            $domain = end($emailParts);
            
            // Only allow domains containing "ecoride" to be set as admin
            if (stripos($domain, 'ecoride') === false) {
                $_SESSION['error'] = "Seuls les utilisateurs avec un domaine d'email contenant 'ecoride' peuvent être définis comme administrateurs.";
                header('Location: /admin/users');
                exit;
            }
        }
        
        if ($this->userModel->toggleAdminStatus($userId)) {
            $_SESSION['success'] = "Le statut d'administrateur a été modifié avec succès.";
        } else {
            $_SESSION['error'] = "Une erreur est survenue lors de la modification du statut d'administrateur.";
        }
        
        // Redirect back to users page
        header('Location: /admin/users');
        exit;
    }
    
    /**
     * List all rides with management options
     */
    public function rides() {
        $pageTitle = 'EcoRide - Gestion des trajets';
        $currentPage = 'admin-rides';
        
        // Get all rides
        $rides = $this->rideModel->getAllRidesWithDetails();
        
        // Start output buffering
        ob_start();
        
        // Include the view
        require_once BASE_PATH . '/app/views/admin/rides.php';
        
        // Get the buffered content and clean the buffer
        $content = ob_get_clean();
        
        // Include the admin layout template
        require_once BASE_PATH . '/app/views/layouts/admin.php';
    }
    
    /**
     * Delete a ride
     */
    public function deleteRide($rideId) {
        if ($this->rideModel->deleteRide($rideId)) {
            $_SESSION['success'] = "Le trajet a été supprimé avec succès.";
        } else {
            $_SESSION['error'] = "Une erreur est survenue lors de la suppression du trajet.";
        }
        
        // Redirect back to rides page
        header('Location: /admin/rides');
        exit;
    }
    
    /**
     * Update ride status
     */
    public function updateRideStatus($rideId) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $status = $_POST['status'] ?? '';
            
            if (in_array($status, ['pending', 'ongoing', 'completed', 'cancelled'])) {
                if ($this->rideModel->updateRideStatus($rideId, $status)) {
                    $_SESSION['success'] = "Le statut du trajet a été mis à jour avec succès.";
                } else {
                    $_SESSION['error'] = "Une erreur est survenue lors de la mise à jour du statut du trajet.";
                }
            } else {
                $_SESSION['error'] = "Statut non valide.";
            }
        }
        
        // Redirect back to rides page
        header('Location: /admin/rides');
        exit;
    }
    
    /**
     * List all vehicles with management options
     */
    public function vehicles() {
        $pageTitle = 'EcoRide - Gestion des véhicules';
        $currentPage = 'admin-vehicles';
        
        // Get all vehicles
        $vehicles = $this->vehicleModel->getAllVehiclesWithOwners();
        
        // Start output buffering
        ob_start();
        
        // Include the view
        require_once BASE_PATH . '/app/views/admin/vehicles.php';
        
        // Get the buffered content and clean the buffer
        $content = ob_get_clean();
        
        // Include the admin layout template
        require_once BASE_PATH . '/app/views/layouts/admin.php';
    }
    
    /**
     * Delete a vehicle
     */
    public function deleteVehicle($vehicleId) {
        if ($this->vehicleModel->deleteVehicle($vehicleId)) {
            $_SESSION['success'] = "Le véhicule a été supprimé avec succès.";
        } else {
            $_SESSION['error'] = "Une erreur est survenue lors de la suppression du véhicule.";
        }
        
        // Redirect back to vehicles page
        header('Location: /admin/vehicles');
        exit;
    }
    
    /**
     * Toggle eco-friendly status for a vehicle
     */
    public function toggleEcoFriendly($vehicleId) {
        if ($this->vehicleModel->toggleEcoFriendly($vehicleId)) {
            $_SESSION['success'] = "Le statut écologique du véhicule a été modifié avec succès.";
        } else {
            $_SESSION['error'] = "Une erreur est survenue lors de la modification du statut écologique.";
        }
        
        // Redirect back to vehicles page
        header('Location: /admin/vehicles');
        exit;
    }
    
    /**
     * Display user creation form
     */
    public function createUser() {
        $pageTitle = 'EcoRide - Ajouter un utilisateur';
        $currentPage = 'admin-users';
        
        // Start output buffering
        ob_start();
        
        // Include the view
        require_once BASE_PATH . '/app/views/admin/create_user.php';
        
        // Get the buffered content and clean the buffer
        $content = ob_get_clean();
        
        // Include the admin layout template
        require_once BASE_PATH . '/app/views/layouts/admin.php';
    }
    
    /**
     * Process user creation
     */
    public function storeUser() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/users/create');
            exit;
        }
        
        // Get form data
        $userData = [
            'first_name' => $_POST['first_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? '',
            'is_admin' => isset($_POST['is_admin']) ? true : false
        ];
        
        // Validate inputs
        $errors = [];
        if (empty($userData['first_name'])) {
            $errors['first_name'] = 'Le prénom est requis';
        }
        if (empty($userData['last_name'])) {
            $errors['last_name'] = 'Le nom est requis';
        }
        if (empty($userData['email'])) {
            $errors['email'] = 'L\'email est requis';
        } elseif (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'L\'email n\'est pas valide';
        } elseif ($this->userModel->findByEmail($userData['email'])) {
            $errors['email'] = 'Cet email est déjà utilisé';
        }
        if (empty($userData['password'])) {
            $errors['password'] = 'Le mot de passe est requis';
        } elseif (strlen($userData['password']) < 8) {
            $errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères';
        }
        
        // Check if trying to create an admin user
        if ($userData['is_admin']) {
            // Extract domain part (after @) from email
            $emailParts = explode('@', $userData['email']);
            $domain = end($emailParts);
            
            // Check if domain contains "ecoride"
            if (stripos($domain, 'ecoride') === false) {
                $errors['is_admin'] = 'Seuls les utilisateurs avec un domaine d\'email contenant \'ecoride\' peuvent être définis comme administrateurs.';
            }
        }
        
        // If there are errors, go back to the form
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['form_data'] = $userData;
            header('Location: /admin/users/create');
            exit;
        }
        
        // Create the user
        $userId = $this->userModel->createUserFromAdmin($userData);
        
        if ($userId) {
            $_SESSION['success'] = "L'utilisateur a été créé avec succès.";
            header('Location: /admin/users');
        } else {
            $_SESSION['error'] = "Une erreur est survenue lors de la création de l'utilisateur.";
            header('Location: /admin/users/create');
        }
        exit;
    }
    
    /**
     * Display vehicle creation form
     */
    public function createVehicle() {
        $pageTitle = 'EcoRide - Ajouter un véhicule';
        $currentPage = 'admin-vehicles';
        
        // Get all users for the owner selection
        $users = $this->userModel->getAllUsers();
        
        // Start output buffering
        ob_start();
        
        // Include the view
        require_once BASE_PATH . '/app/views/admin/create_vehicle.php';
        
        // Get the buffered content and clean the buffer
        $content = ob_get_clean();
        
        // Include the admin layout template
        require_once BASE_PATH . '/app/views/layouts/admin.php';
    }
    
    /**
     * Process vehicle creation
     */
    public function storeVehicle() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/vehicles/create');
            exit;
        }
        
        // Get form data
        $vehicleData = [
            'user_id' => $_POST['user_id'] ?? '',
            'brand' => $_POST['brand'] ?? '',
            'model' => $_POST['model'] ?? '',
            'year' => $_POST['year'] ?? '',
            'color' => $_POST['color'] ?? '',
            'license_plate' => $_POST['license_plate'] ?? '',
            'seats' => $_POST['seats'] ?? 4,
            'eco_friendly' => isset($_POST['eco_friendly']) ? true : false
        ];
        
        // Validate inputs
        $errors = [];
        if (empty($vehicleData['user_id'])) {
            $errors['user_id'] = 'Le propriétaire est requis';
        }
        if (empty($vehicleData['brand'])) {
            $errors['brand'] = 'La marque est requise';
        }
        if (empty($vehicleData['model'])) {
            $errors['model'] = 'Le modèle est requis';
        }
        if (empty($vehicleData['year'])) {
            $errors['year'] = 'L\'année est requise';
        } elseif (!is_numeric($vehicleData['year']) || $vehicleData['year'] < 1900 || $vehicleData['year'] > date('Y') + 1) {
            $errors['year'] = 'L\'année n\'est pas valide';
        }
        if (empty($vehicleData['license_plate'])) {
            $errors['license_plate'] = 'La plaque d\'immatriculation est requise';
        }
        if (empty($vehicleData['seats']) || !is_numeric($vehicleData['seats']) || $vehicleData['seats'] < 1) {
            $errors['seats'] = 'Le nombre de places doit être au moins 1';
        }
        
        // If there are errors, go back to the form
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['form_data'] = $vehicleData;
            header('Location: /admin/vehicles/create');
            exit;
        }
        
        // Create the vehicle
        $vehicleId = $this->vehicleModel->create($vehicleData);
        
        if ($vehicleId) {
            $_SESSION['success'] = "Le véhicule a été créé avec succès.";
            header('Location: /admin/vehicles');
        } else {
            $_SESSION['error'] = "Une erreur est survenue lors de la création du véhicule.";
            header('Location: /admin/vehicles/create');
        }
        exit;
    }

    /**
     * Show credit requests page
     */
    public function creditRequests() {
        // Verify admin access
        $this->requireAdmin();
        
        // Set page title
        $pageTitle = 'EcoRide Admin - Demandes de crédit';
        $currentPage = 'admin-credit-requests';
        
        // Load models
        require_once BASE_PATH . '/app/models/CreditRequest.php';
        require_once BASE_PATH . '/app/models/User.php';
        
        $requestModel = new \App\Models\CreditRequest();
        $userModel = new \App\Models\User();
        
        // Get pending and processed requests
        $pendingRequests = $requestModel->getAll('pending');
        $processedRequests = array_merge(
            $requestModel->getAll('approved'),
            $requestModel->getAll('rejected')
        );
        
        // Sort processed requests by updated_at date, newest first
        usort($processedRequests, function($a, $b) {
            return strtotime($b['updated_at']) - strtotime($a['updated_at']);
        });
        
        // Get all users for the manual credit form
        $users = $userModel->getAllUsers();
        
        // Start output buffering
        ob_start();
        
        // Include the view
        require_once BASE_PATH . '/app/views/admin/credit_requests.php';
        
        // Get the buffered content and clean the buffer
        $content = ob_get_clean();
        
        // Include the admin layout template
        require_once BASE_PATH . '/app/views/layouts/admin.php';
    }

    /**
     * Process a credit request (approve or reject)
     */
    public function processCreditRequest() {
        // Verify admin access
        $this->requireAdmin();
        
        // Check if this is a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/credit-requests');
            exit;
        }
        
        // Get request ID and action
        $requestId = $_POST['request_id'] ?? null;
        $action = $_POST['action'] ?? null;
        
        if (!$requestId || !in_array($action, ['approve', 'reject'])) {
            $_SESSION['error'] = 'Paramètres invalides';
            header('Location: /admin/credit-requests');
            exit;
        }
        
        // Load models
        require_once BASE_PATH . '/app/models/CreditRequest.php';
        $requestModel = new \App\Models\CreditRequest();
        
        // Get current admin user
        $admin = \App\Helpers\Auth::user();
        
        // Process the request
        $status = $action === 'approve' ? 'approved' : 'rejected';
        $result = $requestModel->processRequest($requestId, $status, $admin['id']);
        
        if ($result) {
            $_SESSION['success'] = 'La demande a été ' . ($action === 'approve' ? 'approuvée' : 'rejetée') . ' avec succès';
        } else {
            $_SESSION['error'] = 'Une erreur est survenue lors du traitement de la demande';
        }
        
        header('Location: /admin/credit-requests');
        exit;
    }

    /**
     * Add credits to a user manually
     */
    public function addCredit() {
        // Verify admin access
        $this->requireAdmin();
        
        // Check if this is a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/credit-requests');
            exit;
        }
        
        // Get form data
        $userId = $_POST['user_id'] ?? null;
        $amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0;
        $description = $_POST['description'] ?? '';
        
        if (!$userId || $amount <= 0 || empty($description)) {
            $_SESSION['error'] = 'Paramètres invalides';
            header('Location: /admin/credit-requests');
            exit;
        }
        
        // Load models
        require_once BASE_PATH . '/app/models/User.php';
        require_once BASE_PATH . '/app/models/CreditTransaction.php';
        
        $userModel = new \App\Models\User();
        
        // Get current admin user
        $admin = \App\Helpers\Auth::user();
        
        // Add credits to user
        $result = $userModel->adjustCredit(
            $userId,
            $amount,
            'admin_adjustment',
            null,
            $description,
            $admin['id']
        );
        
        if ($result) {
            $_SESSION['success'] = 'Les crédits ont été ajoutés avec succès';
        } else {
            $_SESSION['error'] = 'Une erreur est survenue lors de l\'ajout des crédits';
        }
        
        header('Location: /admin/credit-requests');
        exit;
    }

    /**
     * List reviews pending moderation
     */
    public function reviews() {
        $this->requireAdmin();
        
        $pageTitle = 'EcoRide - Modération des commentaires';
        $currentPage = 'admin-reviews';
        
        // Load Rating model
        require_once BASE_PATH . '/app/models/Rating.php';
        $ratingModel = new \App\Models\Rating();
        
        // Get pending reviews
        $pendingReviews = $ratingModel->getPendingRatings(50);
        
        // Start output buffering
        ob_start();
        
        // Include the view
        require_once BASE_PATH . '/app/views/admin/reviews.php';
        
        // Get the buffered content and clean the buffer
        $content = ob_get_clean();
        
        // Include the admin layout template
        require_once BASE_PATH . '/app/views/layouts/admin.php';
    }
    
    /**
     * Moderate a review (approve/reject)
     */
    public function moderateReview() {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/reviews');
            exit;
        }
        
        $ratingId = $_POST['rating_id'] ?? 0;
        $action = $_POST['action'] ?? '';
        $notes = $_POST['notes'] ?? '';
        
        if (!$ratingId || !in_array($action, ['approve', 'reject'])) {
            $_SESSION['error'] = 'Paramètres invalides.';
            header('Location: /admin/reviews');
            exit;
        }
        
        // Load Rating model
        require_once BASE_PATH . '/app/models/Rating.php';
        $ratingModel = new \App\Models\Rating();
        
        $status = $action === 'approve' ? 'approved' : 'rejected';
        $adminId = \App\Helpers\Auth::user()['id'];
        
        if ($ratingModel->moderate($ratingId, $status, $adminId, $notes)) {
            $_SESSION['success'] = "L'avis a été " . ($action === 'approve' ? 'approuvé' : 'rejeté') . " avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la modération de l'avis.";
        }
        
        header('Location: /admin/reviews');
        exit;
    }
    
    /**
     * Delete a review
     */
    public function deleteReview($ratingId) {
        $this->requireAdmin();
        
        // Load Rating model
        require_once BASE_PATH . '/app/models/Rating.php';
        $ratingModel = new \App\Models\Rating();
        
        if ($ratingModel->delete($ratingId)) {
            $_SESSION['success'] = "L'avis a été supprimé avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression de l'avis.";
        }
        
        header('Location: /admin/reviews');
        exit;
    }
} 