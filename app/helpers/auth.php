<?php

namespace App\Helpers;

/**
 * Authentication Helper
 * 
 * This file contains helper functions for user authentication.
 */

class Auth {
    /**
     * Start a new session if one doesn't exist
     */
    public static function init() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Log in a user
     * 
     * @param array $user User data to store in session
     * @return void
     */
    public static function login($user) {
        self::init();
        $_SESSION['user'] = [
            'id' => $user['id'],
            'email' => $user['email'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'is_admin' => $user['is_admin'], // Ajout de la colonne 'is_admin'
            'logged_in' => true
        ];
    }
    
    /**
     * Log out the current user
     * 
     * @return void
     */
    public static function logout() {
        self::init();
        unset($_SESSION['user']);
        session_destroy();
    }
    
    /**
     * Check if a user is logged in
     * 
     * @return bool
     */
    public static function isLoggedIn() {
        self::init();
        return isset($_SESSION['user']) && $_SESSION['user']['logged_in'] === true;
    }
    
    /**
     * Check if the logged-in user is an admin
     * 
     * @return bool
     */
    public static function isAdmin() {
        self::init();
        return self::isLoggedIn() && isset($_SESSION['user']['is_admin']) && 
               ($_SESSION['user']['is_admin'] === true || $_SESSION['user']['is_admin'] === 1 || $_SESSION['user']['is_admin'] === '1');
    }
    
    /**
     * Get current user data
     * 
     * @return array|null User data or null if not logged in
     */
    public static function user() {
        self::init();
        return self::isLoggedIn() ? $_SESSION['user'] : null;
    }
    
    /**
     * Redirect if user is not logged in
     * 
     * @param string $redirect URL to redirect to
     * @return void
     */
    public static function requireLogin($redirect = '/connexion') {
        if (!self::isLoggedIn()) {
            header("Location: $redirect");
            exit;
        }
    }
}