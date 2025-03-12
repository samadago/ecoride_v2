<?php

namespace App\Controllers;


class PageController {
    /**
     * Display the legal mentions page
     */
    public function legalMentions() {
        // Set current page for navigation highlighting
        $currentPage = 'legal-mentions';
        $pageTitle = 'EcoRide - Mentions Légales';
        
        // Start output buffering
        ob_start();
        
        // Include the view
        require_once BASE_PATH . '/app/views/pages/legalMentions.php';
        
        // Get the buffered content and clean the buffer
        $content = ob_get_clean();
        
        // Include the layout template with the captured content
        require_once BASE_PATH . '/app/views/layouts/main.php';
    }

    /**
     * Display the about page
     */
    public function about() {
        // Set current page for navigation highlighting
        $currentPage = 'about';
        $pageTitle = 'EcoRide - À propos de nous';
        
        // Start output buffering
        ob_start();
        
        // Include the view
        require_once BASE_PATH . '/app/views/pages/about.php';
        
        // Get the buffered content and clean the buffer
        $content = ob_get_clean();
        
        // Include the layout template with the captured content
        require_once BASE_PATH . '/app/views/layouts/main.php';
    }

    /**
     * Display other static pages
     * Add more methods here for other static pages
     */
    public function privacy() {
        require_once __DIR__ . '/../views/pages/privacy.php';
    }

    public function terms() {
        require_once __DIR__ . '/../views/pages/terms.php';
    }
}