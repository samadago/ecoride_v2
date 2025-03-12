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
    
    public function __construct() {
        $this->rideModel = new Ride();
        $this->userModel = new User();
        $this->bookingModel = new Booking();
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
            
            if (empty($errors)) {
                // Create booking record
                $bookingData = [
                    'ride_id' => $rideId,
                    'passenger_id' => $user['id'],
                    'status' => 'pending',
                    'seats_booked' => $seatsBooked,
                    'booking_time' => date('Y-m-d H:i:s')
                ];
                
                if ($this->bookingModel->create($bookingData)) {
                    $_SESSION['success'] = 'Votre demande de réservation a été envoyée au conducteur';
                    header('Location: /profil');
                    exit;
                } else {
                    $errors['booking'] = 'Une erreur est survenue lors de la réservation';
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
}