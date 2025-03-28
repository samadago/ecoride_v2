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
    
    // Booking routes
    '/reserver-trajet' => ['controller' => 'BookingController', 'action' => 'create']
];

return $routes;