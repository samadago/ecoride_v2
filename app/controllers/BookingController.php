<?php

namespace App\Controllers;

use App\Models\Ride;
use App\Models\User;
use App\Models\Booking;
use App\Helpers\Auth;

/**
 * Booking Controller
 * 
 * This controller handles ride booking functionality.
 */
class BookingController {
    private $rideModel;
    private $userModel;
    private $bookingModel;
    private $db;
    
    public function __construct() {
        $this->rideModel = new Ride();
        $this->userModel = new User();
        $this->bookingModel = new Booking();
        $this->db = \App\Config\Database::getInstance();
    }
    
    public function create() {
        // Check if user is logged in
        Auth::requireLogin();
        
        // Set page title and current page
        $pageTitle = 'EcoRide - Réserver un trajet';
        $currentPage = 'booking';
        $errors = [];
        
        // Get the ride ID from POST/GET parameters
        $rideId = $_POST['ride_id'] ?? $_GET['ride_id'] ?? null;
        
        if (!$rideId) {
            header('Location: /covoiturages');
            exit;
        }
        
        // Get ride details
        $ride = $this->rideModel->getById($rideId);
        
        if (!$ride) {
            header('Location: /covoiturages');
            exit;
        }

        // Map ride data to expected view fields
        $ride['departure_city'] = $ride['departure_location'];
        $ride['arrival_city'] = $ride['arrival_location'];
        $ride['departure_date'] = $ride['departure_time'];
        $ride['departure_time'] = $ride['departure_time'];
        
        // Get current user
        $user = Auth::user();
        
        // Process booking request
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get seats_booked from form
            $seatsBooked = isset($_POST['seats_booked']) ? (int)$_POST['seats_booked'] : 1;
            
            // Calculate total price
            $totalPrice = $ride['price'] * $seatsBooked;
            
            // Validate booking
            if ($ride['user_id'] === $user['id']) {
                $errors['booking'] = 'Vous ne pouvez pas réserver votre propre trajet';
            }
            
            if ($ride['remaining_seats'] < $seatsBooked) {
                $errors['booking'] = 'Désolé, il n\'y a pas assez de places disponibles';
            }
            
            // Check if user already has a booking for this ride
            if ($this->bookingModel->checkExistingBooking($rideId, $user['id'])) {
                $errors['booking'] = 'Vous avez déjà une réservation pour ce trajet';
            }
            
            // Check if user has enough credit
            if (!$this->userModel->hasEnoughCredit($user['id'], $totalPrice)) {
                $errors['booking'] = 'Vous n\'avez pas assez de crédit pour cette réservation. Solde actuel: ' . $this->userModel->getCredit($user['id']) . ' €';
            }
            
            if (empty($errors)) {
                try {
                    // Start transaction
                    $this->db->beginTransaction();
                    
                    // Create booking record
                    $bookingData = [
                        'ride_id' => $rideId,
                        'passenger_id' => $user['id'],
                        'status' => 'pending',
                        'seats_booked' => $seatsBooked,
                        'booking_time' => date('Y-m-d H:i:s')
                    ];
                    
                    $bookingId = $this->bookingModel->create($bookingData);
                    
                    if (!$bookingId) {
                        throw new \Exception('Failed to create booking');
                    }
                    
                    // Process payment - deduct credits from passenger
                    $description = "Réservation pour trajet de {$ride['departure_location']} à {$ride['arrival_location']}";
                    $deductResult = $this->userModel->adjustCredit(
                        $user['id'],
                        -$totalPrice,
                        'booking',
                        $bookingId,
                        $description,
                        $ride['user_id']
                    );
                    
                    if (!$deductResult) {
                        throw new \Exception('Failed to process payment');
                    }
                    
                    $this->db->commit();
                    
                    $_SESSION['success'] = 'Votre demande de réservation a été envoyée au conducteur';
                    header('Location: /profil');
                    exit;
                } catch (\Exception $e) {
                    $this->db->rollBack();
                    $errors['booking'] = 'Une erreur est survenue lors de la réservation: ' . $e->getMessage();
                }
            }
        }
        
        // Start output buffering
        ob_start();
        
        // Include the view
        require_once BASE_PATH . '/app/views/bookings/create.php';
        
        // Get the buffered content and clean the buffer
        $content = ob_get_clean();
        
        // Include the layout template
        require_once BASE_PATH . '/app/views/layouts/main.php';
    }
    
    public function manageBookings() {
        // Check if user is logged in
        Auth::requireLogin();
        
        // Set page title and current page
        $pageTitle = 'EcoRide - Gérer les réservations';
        $currentPage = 'manage-bookings';
        
        // Get current user
        $user = Auth::user();
        
        // Get all bookings for rides where user is the driver
        $bookings = $this->bookingModel->getByDriverId($user['id']);
        
        // Process booking status updates
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $bookingId = $_POST['booking_id'] ?? null;
            $action = $_POST['action'] ?? null;
            
            if ($bookingId && $action) {
                $status = $action === 'accept' ? 'confirmed' : 'declined';
                
                if ($this->bookingModel->updateStatus($bookingId, $status)) {
                    // If booking is confirmed, update remaining seats
                    if ($status === 'confirmed') {
                        $booking = $this->bookingModel->getById($bookingId);
                        $this->rideModel->updateRemainingSeats($booking['ride_id']);
                    }
                    
                    $_SESSION['success'] = 'Le statut de la réservation a été mis à jour';
                    header('Location: /gerer-reservations');
                    exit;
                }
            }
        }
        
        // Start output buffering
        ob_start();
        
        // Include the view
        require_once BASE_PATH . '/app/views/bookings/manage.php';
        
        // Get the buffered content and clean the buffer
        $content = ob_get_clean();
        
        // Include the layout template
        require_once BASE_PATH . '/app/views/layouts/main.php';
    }
    
    public function myBookings() {
        // Check if user is logged in
        Auth::requireLogin();
        
        // Set page title and current page
        $pageTitle = 'EcoRide - Mes réservations';
        $currentPage = 'my-bookings';
        
        // Get current user
        $user = Auth::user();
        
        // Get user's bookings
        $bookings = $this->bookingModel->getByPassengerId($user['id']);
        
        // Start output buffering
        ob_start();
        
        // Include the view
        require_once BASE_PATH . '/app/views/bookings/index.php';
        
        // Get the buffered content and clean the buffer
        $content = ob_get_clean();
        
        // Include the layout template
        require_once BASE_PATH . '/app/views/layouts/main.php';
    }

    /**
     * Cancel a booking
     */
    public function cancelBooking() {
        // Check if user is logged in
        Auth::requireLogin();
        
        // Set page title and current page
        $pageTitle = 'EcoRide - Annuler une réservation';
        $currentPage = 'my-bookings';
        
        // Get current user
        $user = Auth::user();
        
        // Get booking ID from POST/GET parameters
        $bookingId = $_POST['booking_id'] ?? $_GET['booking_id'] ?? null;
        
        if (!$bookingId) {
            $_SESSION['error'] = 'ID de réservation manquant';
            header('Location: /profil');
            exit;
        }
        
        // Get booking details
        $booking = $this->bookingModel->getById($bookingId);
        
        if (!$booking) {
            $_SESSION['error'] = 'Réservation introuvable';
            header('Location: /profil');
            exit;
        }
        
        // Check if user is the passenger or admin
        if ($booking['passenger_id'] !== $user['id'] && !$user['is_admin']) {
            $_SESSION['error'] = 'Vous n\'êtes pas autorisé à annuler cette réservation';
            header('Location: /profil');
            exit;
        }
        
        // Get the ride details
        $ride = $this->rideModel->getById($booking['ride_id']);
        
        if (!$ride) {
            $_SESSION['error'] = 'Trajet introuvable';
            header('Location: /profil');
            exit;
        }
        
        try {
            // Start transaction
            $this->db->beginTransaction();
            
            // Update booking status
            $updateResult = $this->bookingModel->updateStatus($bookingId, 'cancelled');
            
            if (!$updateResult) {
                throw new \Exception('Failed to update booking status');
            }
            
            // Calculate refund amount based on who is cancelling
            $totalPrice = $ride['price'] * $booking['seats_booked'];
            $refundAmount = 0;
            
            if ($user['id'] === $booking['passenger_id']) {
                // Passenger cancellation - 75% refund
                $refundAmount = $totalPrice * 0.75;
            } else {
                // Admin or driver cancellation - 100% refund
                $refundAmount = $totalPrice;
            }
            
            // Process refund
            $description = "Remboursement pour annulation de trajet de {$ride['departure_location']} à {$ride['arrival_location']}";
            $refundResult = $this->userModel->adjustCredit(
                $booking['passenger_id'],
                $refundAmount,
                'cancellation',
                $bookingId,
                $description,
                $ride['user_id']
            );
            
            if (!$refundResult) {
                throw new \Exception('Failed to process refund');
            }
            
            $this->db->commit();
            
            $_SESSION['success'] = 'Réservation annulée avec succès' . 
                                  ($user['id'] === $booking['passenger_id'] ? 
                                  ' (Remboursement de ' . number_format($refundAmount, 2) . '€ effectué)' : '');
            
            header('Location: /profil');
            exit;
        } catch (\Exception $e) {
            $this->db->rollBack();
            $_SESSION['error'] = 'Une erreur est survenue lors de l\'annulation: ' . $e->getMessage();
            header('Location: /profil');
            exit;
        }
    }
}