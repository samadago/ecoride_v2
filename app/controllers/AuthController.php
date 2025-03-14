<?php

namespace App\Controllers;

use App\Models\User;

/**
 * Auth Controller
 * 
 * This controller handles user authentication functionality.
 */

class AuthController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    public function register() {
        // Set page title and current page
        $pageTitle = 'EcoRide - Inscription';
        $currentPage = 'register';
        $errors = [];
        
        // Process form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get form data
            $firstName = trim($_POST['first_name'] ?? '');
            $lastName = trim($_POST['last_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $confirmPassword = trim($_POST['password_confirm'] ?? '');
            
            // Validate inputs
            if (empty($firstName)) {
                $errors['first_name'] = 'Le prénom est requis';
            }
            if (empty($lastName)) {
                $errors['last_name'] = 'Le nom est requis';
            }
            if (empty($email)) {
                $errors['email'] = 'L\'email est requis';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'L\'email n\'est pas valide';
            } elseif ($this->userModel->findByEmail($email)) {
                $errors['email'] = 'Cet email est déjà utilisé';
            }
            if (empty($password)) {
                $errors['password'] = 'Le mot de passe est requis';
            } elseif (strlen($password) < 8) {
                $errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères';
            }
            if ($password !== $confirmPassword) {
                $errors['password_confirm'] = 'Les mots de passe ne correspondent pas';
            }
            
            // If no validation errors, create the user
            if (empty($errors)) {
                // Hash the password
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                
                // Prepare user data
                $userData = [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'password_hash' => $passwordHash,
                    'profile_image' => $_FILES['profile_image'] ?? null
                ];
                
                // Register the user
                if ($this->userModel->register($userData)) {
                    // Set success message in session
                    $_SESSION['success'] = 'Votre compte a été créé avec succès. Vous pouvez maintenant vous connecter.';
                    
                    // Redirect to login page
                    header('Location: /connexion');
                    exit;
                } else {
                    $errors['register'] = 'Une erreur est survenue lors de la création du compte';
                }
            }
        }
        
        // Start output buffering
        ob_start();
        
        // Include the view
        require_once BASE_PATH . '/app/views/auth/register.php';
        
        // Get the buffered content and clean the buffer
        $content = ob_get_clean();
        
        // Include the layout template
        require_once BASE_PATH . '/app/views/layouts/main.php';
    }
    
    public function login() {
        // Set page title and current page
        $pageTitle = 'EcoRide - Connexion';
        $currentPage = 'login';
        $errors = [];
        
        // Process form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            // Validate inputs
            if (empty($email)) {
                $errors['email'] = 'L\'email est requis';
            }
            if (empty($password)) {
                $errors['password'] = 'Le mot de passe est requis';
            }
            
            // If no validation errors, attempt login
            if (empty($errors)) {
                $user = $this->userModel->findByEmail($email);
                
                if ($user && password_verify($password, $user['password_hash'])) {
                    // Use Auth helper to handle login
                    \App\Helpers\Auth::login($user);
                    
                    // Redirect to profile page
                    header('Location: /profil');
                    exit;
                } else {
                    $errors['login'] = 'Email ou mot de passe incorrect';
                }
            }
        }
        
        // Start output buffering
        ob_start();
        
        // Include the view
        require_once BASE_PATH . '/app/views/auth/login.php';
        
        // Get the buffered content and clean the buffer
        $content = ob_get_clean();
        
        // Include the layout template
        require_once BASE_PATH . '/app/views/layouts/main.php';
    }
    
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Clear all session data
        $_SESSION = array();
        
        // Destroy the session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        // Destroy the session
        session_destroy();
        
        // Ensure no output has been sent before redirecting
        if (!headers_sent()) {
            header('Location: /');
            exit;
        } else {
            echo '<script>window.location.href = "/";</script>';
            exit;
        }
    }
}