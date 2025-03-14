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

// Autoload classes
require_once BASE_PATH . '/vendor/autoload.php';

// Custom autoloader for any remaining classes
spl_autoload_register(function ($class) {
    // Convert namespace separators to directory separators
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    
    // Map namespace roots to directories
    $namespaceMap = [
        'App\\Models\\' => BASE_PATH . '/app/models/',
        'App\\Helpers\\' => BASE_PATH . '/app/helpers/',
        'App\\Config\\' => BASE_PATH . '/app/config/',
        'App\\Controllers\\' => BASE_PATH . '/app/controllers/'
    ];
    
    // Check each namespace mapping
    foreach ($namespaceMap as $namespace => $directory) {
        if (strpos($class, $namespace) === 0) {
            // Remove namespace prefix and add .php extension
            $classPath = $directory . substr($class, strlen($namespace)) . '.php';
            if (file_exists($classPath)) {
                require_once $classPath;
                return true;
            }
        }
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
        $controllerClass = "\\App\\Controllers\\" . $controllerName;
        $controllerFile = BASE_PATH . '/app/controllers/' . $controllerName . '.php';
        
        // Include the controller file directly
        if (file_exists($controllerFile)) {
            require_once $controllerFile;
        } else {
            throw new Exception("Controller file not found: {$controllerFile}");
        }
        
        if (!class_exists($controllerClass)) {
            throw new Exception("Controller class not found: {$controllerClass}");
        }
        
        $controller = new $controllerClass();
        
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