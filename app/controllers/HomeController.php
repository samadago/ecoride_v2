<?php

namespace App\Controllers;


/**
 * Home Controller
 * 
 * This controller handles the home page functionality.
 */

class HomeController {
    /**
     * Display the home page
     * 
     * @return void
     */
    public function index() {
        // Set current page for navigation highlighting
        $currentPage = 'home';
        $pageTitle = 'EcoRide - Covoiturage écologique';
        
        // Start output buffering
        ob_start();
        
        // Include the view
        require_once BASE_PATH . '/app/views/home/index.php';
        
        // Get the buffered content and clean the buffer
        $content = ob_get_clean();
        
        // Include the layout template with the captured content
        require_once BASE_PATH . '/app/views/layouts/main.php';
    }
}