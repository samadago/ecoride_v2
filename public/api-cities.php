<?php

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set header to return JSON
header('Content-Type: application/json');

// Get search query from request
$query = $_GET['q'] ?? '';

if (empty($query) || strlen($query) < 2) {
    echo json_encode(['error' => 'Query too short']);
    exit;
}

// Call the French Geo API to get cities
$apiUrl = "https://geo.api.gouv.fr/communes?nom=" . urlencode($query) . "&fields=nom,code,codesPostaux,centre,departement&boost=population&limit=10";

// Initialize curl session
$ch = curl_init();

// Set curl options
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

// Execute curl request
$response = curl_exec($ch);

// Check for errors
if (curl_errno($ch)) {
    echo json_encode(['error' => 'Error fetching cities: ' . curl_error($ch)]);
    curl_close($ch);
    exit;
}

// Close curl session
curl_close($ch);

// Decode JSON response
$cities = json_decode($response, true);

// Format the response
$formattedCities = [];
foreach ($cities as $city) {
    $postalCode = isset($city['codesPostaux'][0]) ? $city['codesPostaux'][0] : '';
    $departement = isset($city['departement']['nom']) ? $city['departement']['nom'] : '';
    
    $formattedCities[] = [
        'name' => $city['nom'],
        'fullName' => $city['nom'] . (empty($postalCode) ? '' : ' (' . $postalCode . ')') . (empty($departement) ? '' : ', ' . $departement),
        'postalCode' => $postalCode,
        'departement' => $departement,
        'coordinates' => $city['centre']['coordinates'] ?? null
    ];
}

// Return JSON response
echo json_encode($formattedCities); 