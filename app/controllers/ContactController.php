<?php

namespace App\Controllers;
use App\Helpers\Auth;

/**
 * Contact Controller
 * 
 * This controller handles contact page functionality.
 */

class ContactController {
    /**
     * Display the contact page
     * 
     * @return void
     */
    public function index() {
        // Set current page for navigation highlighting
        $currentPage = 'contact';
        $pageTitle = 'EcoRide - Contact';
        
        // Start output buffering
        ob_start();
        
        // Include the view
        require_once BASE_PATH . '/app/views/contact/index.php';
        
        // Get the buffered content and clean the buffer
        $content = ob_get_clean();
        
        // Include the layout template
        require_once BASE_PATH . '/app/views/layouts/main.php';
    }
}