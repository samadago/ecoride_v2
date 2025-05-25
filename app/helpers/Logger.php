<?php

namespace App\Helpers;

/**
 * Logger Helper
 * 
 * Provides logging functionality for the application
 */
class Logger {
    /**
     * Log a message to the error log
     * 
     * @param string $message The message to log
     * @param string $level The log level (error, warning, info)
     * @return void
     */
    public static function log($message, $level = 'error') {
        $formattedMessage = '[' . strtoupper($level) . '] ' . $message;
        error_log($formattedMessage);
    }
}

/**
 * Helper function for easy logging
 * 
 * @param string $message The message to log
 * @param string $level The log level (error, warning, info)
 * @return void
 */
function log_message($message, $level = 'error') {
    Logger::log($message, $level);
} 