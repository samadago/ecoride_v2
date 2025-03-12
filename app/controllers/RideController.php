<?php

namespace App\Controllers;

/**
 * Ride Controller
 * 
 * This controller handles ride-related functionality.
 */

// Make sure the Ride model is loaded
require_once BASE_PATH . '/app/models/Ride.php';

class RideController {
    public function index() {
        // Set current page for navigation highlighting
        $currentPage = 'rides';
        $pageTitle = 'EcoRide - Covoiturages';
        
        // Create an instance of the Ride model
        $rideModel = new \App\Models\Ride();
        
        // Get all rides with vehicle information
        $rides = $rideModel->getAllWithVehicles();
        
        // Get search parameters if any
        $departure = $_GET['departure'] ?? '';
        $arrival = $_GET['arrival'] ?? '';
        $date = $_GET['date'] ?? date('Y-m-d');
        
        // If search parameters are provided, filter the rides
        if (!empty($departure) && !empty($arrival)) {
            $rides = $rideModel->search($departure, $arrival, $date);
        }
        
        // Start output buffering
        ob_start();
        
        // Include the view
        require_once BASE_PATH . '/app/views/rides/index.php';
        
        // Get the buffered content and clean the buffer
        $content = ob_get_clean();
        
        // Include the layout template
        require_once BASE_PATH . '/app/views/layouts/main.php';
    }

    public function show($id = null) {
        // Set page title
        $pageTitle = 'EcoRide - Détails du trajet';
        $currentPage = 'rides';

        // Check if ID is provided in the URL query string
        if ($id === null && isset($_GET['id'])) {
            $id = (int)$_GET['id'];
        }
        
        // Redirect to rides list if no ID is provided
        if ($id === null) {
            header('Location: /covoiturages');
            exit;
        }

        // Create an instance of the Ride model
        $rideModel = new \App\Models\Ride();
        
        // Get the ride details
        $ride = $rideModel->getById($id);
        
        if (!$ride) {
            // Redirect to rides list if ride not found
            header('Location: /covoiturages');
            exit;
        }

        // Start output buffering
        ob_start();
        
        // Include the view
        require_once BASE_PATH . '/app/views/rides/show.php';
        
        // Get the buffered content and clean the buffer
        $content = ob_get_clean();
        
        // Include the layout template
        require_once BASE_PATH . '/app/views/layouts/main.php';
    }

    public function create() {
        // Set page title and current page
        $pageTitle = 'EcoRide - Proposer un trajet';
        $currentPage = 'create';
        $errors = [];
        
        // Check if user is logged in
        \App\Helpers\Auth::requireLogin();
        
        // Get current user's ID
        $user = \App\Helpers\Auth::user();
        $userId = $user['id'];
        
        // Load Vehicle model and get user's vehicles
        require_once BASE_PATH . '/app/models/Vehicle.php';
        $vehicleModel = new \App\Models\Vehicle();
        $vehicles = $vehicleModel->getByUserId($userId);

        // Process form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get form data
            $departureLocation = $_POST['departure_location'] ?? '';
            $arrivalLocation = $_POST['arrival_location'] ?? '';
            $departureTime = $_POST['departure_time'] ?? '';
            $seatsAvailable = $_POST['seats_available'] ?? '';
            $vehicleId = $_POST['vehicle_id'] ?? '';
            $price = $_POST['price'] ?? '';
            $estimatedArrivalTime = $_POST['estimated_arrival_time'] ?? '';
            $ecoFriendly = isset($_POST['eco_friendly']) ? 1 : 0;

            // Validate form data
            if (empty($departureLocation)) {
                $errors[] = 'Le lieu de départ ne peut pas être vide.';
            }
            if (empty($arrivalLocation)) {
                $errors[] = 'Le lieu d\'arrivée ne peut pas être vide.';
            }
            if (empty($departureTime)) {
                $errors[] = 'L\'heure de départ ne peut pas être vide.';
            }
            if (empty($seatsAvailable) || !is_numeric($seatsAvailable)) {
                $errors[] = 'Le nombre de sièges disponibles doit être un nombre.';
            }
            if (empty($vehicleId)) {
                $errors[] = 'Vous devez sélectionner un véhicule.';
            }

            // If no errors, save the ride to the database
            if (empty($errors)) {
                $rideModel = new \App\Models\Ride();
                $rideModel->user_id = $userId;
                $rideModel->vehicle_id = $vehicleId;
                $rideModel->departure_location = $departureLocation;
                $rideModel->arrival_location = $arrivalLocation;
                $rideModel->departure_time = $departureTime;
                $rideModel->estimated_arrival_time = $estimatedArrivalTime;
                $rideModel->price = $price;
                $rideModel->seats_available = $seatsAvailable;
                $rideModel->eco_friendly = $ecoFriendly;
                $rideModel->save();

                // Redirect to rides list
                header('Location: /covoiturages');
                exit;
            }
        }

        // Start output buffering
        ob_start();

        // Include the view
        require_once BASE_PATH . '/app/views/rides/create.php';

        // Get the buffered content and clean the buffer
        $content = ob_get_clean();

        // Include the layout template
        require_once BASE_PATH . '/app/views/layouts/main.php';
    }
}