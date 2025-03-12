<?php

/**
 * Validation Helper
 * 
 * This file contains helper functions for validating form inputs.
 */

class Validation {
    private $errors = [];
    
    /**
     * Check if a field is not empty
     * 
     * @param string $field The field name
     * @param string $value The field value
     * @param string $message Custom error message (optional)
     * @return $this
     */
    public function required($field, $value, $message = null) {
        if ($value === null || trim((string)$value) === '') {
            $this->errors[$field] = $message ?: "Le champ {$field} est obligatoire.";
        }
        return $this;
    }
    
    /**
     * Check if a field is a valid email
     * 
     * @param string $field The field name
     * @param string $value The field value
     * @param string $message Custom error message (optional)
     * @return $this
     */
    public function email($field, $value, $message = null) {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $message ?: "L'adresse email n'est pas valide.";
        }
        return $this;
    }
    
    /**
     * Check if a field has minimum length
     * 
     * @param string $field The field name
     * @param string $value The field value
     * @param int $min Minimum length
     * @param string $message Custom error message (optional)
     * @return $this
     */
    public function minLength($field, $value, $min, $message = null) {
        if (!empty($value) && mb_strlen($value) < $min) {
            $this->errors[$field] = $message ?: "Le champ {$field} doit contenir au moins {$min} caractères.";
        }
        return $this;
    }
    
    /**
     * Check if a field has maximum length
     * 
     * @param string $field The field name
     * @param string $value The field value
     * @param int $max Maximum length
     * @param string $message Custom error message (optional)
     * @return $this
     */
    public function maxLength($field, $value, $max, $message = null) {
        if (!empty($value) && mb_strlen($value) > $max) {
            $this->errors[$field] = $message ?: "Le champ {$field} ne doit pas dépasser {$max} caractères.";
        }
        return $this;
    }
    
    /**
     * Check if a field matches another field (e.g., password confirmation)
     * 
     * @param string $field The field name
     * @param string $value The field value
     * @param string $matchValue The value to match against
     * @param string $message Custom error message (optional)
     * @return $this
     */
    public function matches($field, $value, $matchValue, $message = null) {
        if ($value !== $matchValue) {
            $this->errors[$field] = $message ?: "Les champs ne correspondent pas.";
        }
        return $this;
    }
    
    /**
     * Check if a field contains only alphanumeric characters
     * 
     * @param string $field The field name
     * @param string $value The field value
     * @param string $message Custom error message (optional)
     * @return $this
     */
    public function alphaNumeric($field, $value, $message = null) {
        if (!empty($value) && !ctype_alnum($value)) {
            $this->errors[$field] = $message ?: "Le champ {$field} doit contenir uniquement des caractères alphanumériques.";
        }
        return $this;
    }
    
    /**
     * Check if a field is a valid date
     * 
     * @param string $field The field name
     * @param string $value The field value
     * @param string $format The date format (default: Y-m-d)
     * @param string $message Custom error message (optional)
     * @return $this
     */
    public function date($field, $value, $format = 'Y-m-d', $message = null) {
        if (!empty($value)) {
            $date = DateTime::createFromFormat($format, $value);
            if (!$date || $date->format($format) !== $value) {
                $this->errors[$field] = $message ?: "Le format de date n'est pas valide.";
            }
        }
        return $this;
    }
    
    /**
     * Check if a field is a valid datetime
     * 
     * @param string $field The field name
     * @param string $value The field value
     * @param string $message Custom error message (optional)
     * @return $this
     */
    public function datetime($field, $value, $message = null) {
        return $this->date($field, $value, 'Y-m-d\TH:i', $message ?: "Le format de date et heure n'est pas valide.");
    }
    
    /**
     * Check if validation passed
     * 
     * @return bool
     */
    public function passes() {
        return empty($this->errors);
    }
    
    /**
     * Check if validation failed
     * 
     * @return bool
     */
    public function fails() {
        return !$this->passes();
    }
    
    /**
     * Get all validation errors
     * 
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Get a specific error
     * 
     * @param string $field The field name
     * @return string|null
     */
    public function getError($field) {
        return $this->errors[$field] ?? null;
    }
    
    /**
     * Sanitize input data
     * 
     * @param string $data The data to sanitize
     * @return string
     */
    public static function sanitize($data) {
        if ($data === null) {
            return '';
        }
        return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
    }
}