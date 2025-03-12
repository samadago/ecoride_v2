<?php

/**
 * Main Entry Point
 * 
 * This file serves as the front controller for the EcoRide application.
 * It handles all incoming requests and routes them to the appropriate controllers.
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Load environment variables
$dotenv = BASE_PATH . '/.env';
if (file_exists($dotenv)) {
    $lines = file($dotenv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0 || empty($line)) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value, " \t\n\r\0\x0B\"");
            if (!empty($name)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

// Base path is already defined above

// Autoload classes
spl_autoload_register(function ($class) {
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    
    // First try to load from the base path (for namespaced classes)
    $file = BASE_PATH . DIRECTORY_SEPARATOR . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    
    // Then try to load from models directory
    $modelFile = BASE_PATH . '/app/models/' . $class . '.php';
    if (file_exists($modelFile)) {
        require_once $modelFile;
        return true;
    }
    
    // Then try to load from helpers directory
    $helperFile = BASE_PATH . '/app/helpers/' . $class . '.php';
    if (file_exists($helperFile)) {
        require_once $helperFile;
        return true;
    }
    
    // Then try to load from config directory
    $configFile = BASE_PATH . '/app/config/' . $class . '.php';
    if (file_exists($configFile)) {
        require_once $configFile;
        return true;
    }
    
    return false;
}); 

// Load configuration
$routes = require_once BASE_PATH . '/app/config/routes.php';

// Start session
session_start();

// Get the current URI
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove base path from URI if needed
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath !== '/' && strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}

// Default to home if URI is empty
if (empty($uri) || $uri === '/') {
    $uri = '/';
}

// Route the request
$routeFound = false;

if (isset($routes[$uri])) {
    $route = $routes[$uri];
    $controllerName = $route['controller'];
    $actionName = $route['action'];
    $params = $route['params'] ?? [];
    $routeFound = true;
} else {
    // Check for pattern routes
    foreach ($routes as $pattern => $routeInfo) {
        // Skip non-pattern routes
        if (strpos($pattern, '(') === false) {
            continue;
        }
        
        // Convert route pattern to regex
        $regex = '#^' . $pattern . '$#';
        
        if (preg_match($regex, $uri, $matches)) {
            $route = $routeInfo;
            $controllerName = $route['controller'];
            $actionName = $route['action'];
            
            // Extract parameters
            $params = [];
            if (isset($route['params'])) {
                foreach ($route['params'] as $paramName => $matchIndex) {
                    $params[$paramName] = $matches[$matchIndex];
                }
            }
            
            $routeFound = true;
            break;
        }
    }
}

if ($routeFound) {
    try {
        // Load the controller
        $controllerFile = BASE_PATH . '/app/controllers/' . $controllerName . '.php';
        if (!file_exists($controllerFile)) {
            throw new Exception("Controller file not found: {$controllerName}");
        }
        require_once $controllerFile;
        
        // Create controller instance and call action
        $controllerName = "\\App\\Controllers\\" . $controllerName;
        if (!class_exists($controllerName)) {
            throw new Exception("Controller class not found: {$controllerName}");
        }
        $controller = new $controllerName();
        
        if (!method_exists($controller, $actionName)) {
            throw new Exception("Action method not found: {$actionName}");
        }
        
        // Call the action with parameters if they exist
        if (!empty($params)) {
            call_user_func_array([$controller, $actionName], $params);
        } else {
            $controller->$actionName();
        }
    } catch (Exception $e) {
        // Log the error and display a user-friendly message
        error_log($e->getMessage());
        header('HTTP/1.1 500 Internal Server Error');
        echo '<h1>Internal Server Error</h1>';
        echo '<p>An error occurred while processing your request. Please try again later.</p>';
        if (getenv('APP_ENV') === 'development') {
            echo '<pre>' . $e->getMessage() . '</pre>';
        }
    }
} else {
    // 404 Not Found
    header('HTTP/1.0 404 Not Found');
    echo '<h1>Page Not Found</h1>';
    echo '<p>The page you requested could not be found.</p>';
    echo '<p><a href="/">Return to Home</a></p>';
}