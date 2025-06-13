<?php

/**
 * Routes Configuration
 * 
 * This file defines the routes for the EcoRide application.
 * Each route maps a URL pattern to a controller and action.
 */

$routes = [
    // Public routes
    '/' => ['controller' => 'HomeController', 'action' => 'index'],
    '/covoiturages' => ['controller' => 'RideController', 'action' => 'index'],
    '/detail-covoiturage' => ['controller' => 'RideController', 'action' => 'show'],
    '/detail-covoiturage/([0-9]+)' => ['controller' => 'RideController', 'action' => 'show', 'params' => ['id' => 1]],
    '/proposer-trajet' => ['controller' => 'RideController', 'action' => 'create'],
    
    // Authentication routes
    '/inscription' => ['controller' => 'AuthController', 'action' => 'register'],
    '/connexion' => ['controller' => 'AuthController', 'action' => 'login'],
    '/deconnexion' => ['controller' => 'AuthController', 'action' => 'logout'],
    
    // Contact route
    '/contact' => ['controller' => 'ContactController', 'action' => 'index'],
    
    // Legal mentions and About Us
    '/mentions-legales' => ['controller' => 'PageController', 'action' => 'legalMentions'],
    '/a-propos' => ['controller' => 'PageController', 'action' => 'about'],
    
    // Profile routes
    '/profil' => ['controller' => 'ProfileController', 'action' => 'index'],
    '/profil/update' => ['controller' => 'ProfileController', 'action' => 'update'],
    '/profil/credits' => ['controller' => 'ProfileController', 'action' => 'credit'],
    '/profil/demande-credit' => ['controller' => 'ProfileController', 'action' => 'requestCredit'],
    '/profil/credit-request' => ['controller' => 'ProfileController', 'action' => 'requestCredit'],
    
    // Booking routes
    '/reserver-trajet' => ['controller' => 'BookingController', 'action' => 'create'],
    
    // Admin routes
    '/admin' => ['controller' => 'AdminController', 'action' => 'dashboard'],
    '/admin/users' => ['controller' => 'AdminController', 'action' => 'users'],
    '/admin/users/create' => ['controller' => 'AdminController', 'action' => 'createUser'],
    '/admin/users/store' => ['controller' => 'AdminController', 'action' => 'storeUser'],
    '/admin/users/delete/([0-9]+)' => ['controller' => 'AdminController', 'action' => 'deleteUser', 'params' => ['userId' => 1]],
    '/admin/users/toggle-admin/([0-9]+)' => ['controller' => 'AdminController', 'action' => 'toggleAdminStatus', 'params' => ['userId' => 1]],
    '/admin/rides' => ['controller' => 'AdminController', 'action' => 'rides'],
    '/admin/rides/delete/([0-9]+)' => ['controller' => 'AdminController', 'action' => 'deleteRide', 'params' => ['rideId' => 1]],
    '/admin/rides/status/([0-9]+)' => ['controller' => 'AdminController', 'action' => 'updateRideStatus', 'params' => ['rideId' => 1]],
    '/admin/vehicles' => ['controller' => 'AdminController', 'action' => 'vehicles'],
    '/admin/vehicles/create' => ['controller' => 'AdminController', 'action' => 'createVehicle'],
    '/admin/vehicles/store' => ['controller' => 'AdminController', 'action' => 'storeVehicle'],
    '/admin/vehicles/delete/([0-9]+)' => ['controller' => 'AdminController', 'action' => 'deleteVehicle', 'params' => ['vehicleId' => 1]],
    '/admin/vehicles/toggle-eco/([0-9]+)' => ['controller' => 'AdminController', 'action' => 'toggleEcoFriendly', 'params' => ['vehicleId' => 1]],
    '/admin/credit-requests' => ['controller' => 'AdminController', 'action' => 'creditRequests'],
    '/admin/credit-requests/process' => ['controller' => 'AdminController', 'action' => 'processCreditRequest'],
    '/admin/credit-add' => ['controller' => 'AdminController', 'action' => 'addCredit'],
    
    // Admin review moderation routes
    '/admin/reviews' => ['controller' => 'AdminController', 'action' => 'reviews'],
    '/admin/reviews/moderate' => ['controller' => 'AdminController', 'action' => 'moderateReview'],
    '/admin/reviews/delete/([0-9]+)' => ['controller' => 'AdminController', 'action' => 'deleteReview', 'params' => ['ratingId' => 1]],
    
    // Review routes
    '/avis/creer' => ['controller' => 'ReviewController', 'action' => 'create'],
    '/avis/store' => ['controller' => 'ReviewController', 'action' => 'store'],
    '/avis/utilisateur' => ['controller' => 'ReviewController', 'action' => 'userReviews'],

    // Add API routes
    '/api/cities' => ['controller' => 'ApiController', 'action' => 'searchCities'],
];

return $routes; 