<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\Rating;
use App\Models\Booking;
use App\Models\Ride;
use App\Models\User;

class ReviewController {
    private $ratingModel;
    private $bookingModel;
    private $rideModel;
    private $userModel;
    
    public function __construct() {
        $this->ratingModel = new Rating();
        $this->bookingModel = new Booking();
        $this->rideModel = new Ride();
        $this->userModel = new User();
    }
    
    /**
     * Show create review form
     */
    public function create() {
        Auth::requireLogin();
        
        $bookingId = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
        $rideId = isset($_GET['ride_id']) ? (int)$_GET['ride_id'] : 0;
        
        if (!$bookingId || !$rideId) {
            $_SESSION['error'] = 'Paramètres manquants pour créer un avis.';
            header('Location: /profil');
            exit;
        }
        
        $userId = Auth::user()['id'];
        
        // Get booking details
        $booking = $this->bookingModel->getById($bookingId);
        if (!$booking) {
            $_SESSION['error'] = 'Réservation introuvable.';
            header('Location: /profil');
            exit;
        }
        
        // Get ride details
        $ride = $this->rideModel->getById($rideId);
        if (!$ride) {
            $_SESSION['error'] = 'Trajet introuvable.';
            header('Location: /profil');
            exit;
        }
        
        // Check if user is involved in this booking
        $isDriver = ($ride['driver_id'] == $userId);
        $isPassenger = ($booking['passenger_id'] == $userId);
        
        if (!$isDriver && !$isPassenger) {
            $_SESSION['error'] = 'Vous n\'êtes pas autorisé à évaluer ce trajet.';
            header('Location: /profil');
            exit;
        }
        
        // Check if ride is completed
        if ($ride['status'] !== 'completed' && $booking['status'] !== 'completed') {
            $_SESSION['error'] = 'Vous ne pouvez évaluer qu\'après la fin du trajet.';
            header('Location: /profil');
            exit;
        }
        
        // Determine who to rate
        if ($isDriver) {
            $userToRate = $this->userModel->getById($booking['passenger_id']);
            $ratingType = 'passenger';
        } else {
            $userToRate = $this->userModel->getById($ride['driver_id']);
            $ratingType = 'driver';
        }
        
        if (!$userToRate) {
            $_SESSION['error'] = 'Utilisateur à évaluer introuvable.';
            header('Location: /profil');
            exit;
        }
        
        // Check if already rated
        $fromUserId = $userId;
        $toUserId = $userToRate['id'];
        
        if ($this->ratingModel->hasRated($fromUserId, $toUserId, $bookingId)) {
            $_SESSION['error'] = 'Vous avez déjà évalué cet utilisateur pour ce trajet.';
            header('Location: /profil');
            exit;
        }
        
        // Pass data to view
        $data = [
            'booking' => $booking,
            'ride' => $ride,
            'userToRate' => $userToRate,
            'ratingType' => $ratingType
        ];
        
        require_once BASE_PATH . '/app/views/reviews/create.php';
    }
    
    /**
     * Store a new review
     */
    public function store() {
        Auth::requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /profil');
            exit;
        }
        
        $bookingId = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
        $toUserId = isset($_POST['to_user_id']) ? (int)$_POST['to_user_id'] : 0;
        $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
        $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
        
        // Validate input
        if (!$bookingId || !$toUserId || !$rating || $rating < 1 || $rating > 5) {
            $_SESSION['error'] = 'Données invalides. Veuillez remplir tous les champs obligatoires.';
            header('Location: /profil');
            exit;
        }
        
        $fromUserId = Auth::user()['id'];
        
        // Security checks
        $booking = $this->bookingModel->getById($bookingId);
        if (!$booking) {
            $_SESSION['error'] = 'Réservation introuvable.';
            header('Location: /profil');
            exit;
        }
        
        $ride = $this->rideModel->getById($booking['ride_id']);
        if (!$ride) {
            $_SESSION['error'] = 'Trajet introuvable.';
            header('Location: /profil');
            exit;
        }
        
        // Check if user is involved in this booking
        $isDriver = ($ride['driver_id'] == $fromUserId);
        $isPassenger = ($booking['passenger_id'] == $fromUserId);
        
        if (!$isDriver && !$isPassenger) {
            $_SESSION['error'] = 'Vous n\'êtes pas autorisé à évaluer ce trajet.';
            header('Location: /profil');
            exit;
        }
        
        // Check if correct user is being rated
        if ($isDriver && $toUserId != $booking['passenger_id']) {
            $_SESSION['error'] = 'Utilisateur à évaluer incorrect.';
            header('Location: /profil');
            exit;
        }
        
        if ($isPassenger && $toUserId != $ride['driver_id']) {
            $_SESSION['error'] = 'Utilisateur à évaluer incorrect.';
            header('Location: /profil');
            exit;
        }
        
        // Check if ride is completed
        if ($ride['status'] !== 'completed' && $booking['status'] !== 'completed') {
            $_SESSION['error'] = 'Vous ne pouvez évaluer qu\'après la fin du trajet.';
            header('Location: /profil');
            exit;
        }
        
        // Create the rating
        $ratingId = $this->ratingModel->create($bookingId, $fromUserId, $toUserId, $rating, $comment);
        
        if ($ratingId) {
            if (empty(trim($comment))) {
                $_SESSION['success'] = 'Votre note a été publiée avec succès !';
            } else {
                $_SESSION['success'] = 'Votre note a été publiée ! Votre commentaire sera vérifié par nos modérateurs avant publication.';
            }
        } else {
            $_SESSION['error'] = 'Erreur lors de la soumission de votre avis. Vous avez peut-être déjà évalué cet utilisateur.';
        }
        
        header('Location: /profil');
        exit;
    }
    
    /**
     * Show user's reviews
     */
    public function userReviews() {
        $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
        
        if (!$userId) {
            header('Location: /');
            exit;
        }
        
        $user = $this->userModel->getById($userId);
        if (!$user) {
            $_SESSION['error'] = 'Utilisateur introuvable.';
            header('Location: /');
            exit;
        }
        
        // Get user's rating summary
        $ratingSummary = $this->ratingModel->getUserRatingSummary($userId);
        
        // Get user's reviews
        $reviews = $this->ratingModel->getUserRatings($userId, 20);
        
        $data = [
            'user' => $user,
            'ratingSummary' => $ratingSummary,
            'reviews' => $reviews
        ];
        
        require_once BASE_PATH . '/app/views/reviews/user_reviews.php';
    }
} 