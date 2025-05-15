<?php
require_once 'config.php'; // Ensure API_KEY and API_BASE_URL are defined here

/**
 * Makes a request to the Valorant API.
 *
 * @param string $endpoint The API endpoint to call (e.g., '/status/{region}').
 * @param array $params Associative array of parameters to include in the query string.
 * @param string $method The HTTP method to use (GET, POST, etc.). Default is GET.
 * @param array $headers Additional headers for the request.
 * @return array|null The decoded JSON response as an associative array, or null on failure.
 */
function callValorantApi($endpoint, $params = [], $method = 'GET', $headers = []) {
    // Revert to using VALORANT_API_KEY constant from config.php
    if (!defined('VALORANT_API_KEY') || VALORANT_API_KEY === 'YOUR_API_KEY' || empty(VALORANT_API_KEY)) {
        error_log('API Key is not configured in config.php or is a placeholder.');
        return ['error' => 'API Key not configured. Please set it as VALORANT_API_KEY in config.php.'];
    }
    $apiKey = VALORANT_API_KEY;

    // API_BASE_URL should also be defined in config.php
    if (!defined('API_BASE_URL')) {
        error_log('API Base URL is not configured in config.php.');
        return ['error' => 'API Base URL not configured. Please set it as API_BASE_URL in config.php.'];
    }
    $url = API_BASE_URL . $endpoint;

    // Add API key to headers
    $defaultHeaders = [
        'Authorization: ' . $apiKey,
        'Content-Type: application/json'
    ];
    $allHeaders = array_merge($defaultHeaders, $headers);

    if (!empty($params) && $method === 'GET') {
        $url .= '?' . http_build_query($params);
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $allHeaders);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    if ($method === 'POST' && !empty($params)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
    }
    
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); 
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); 

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        error_log("cURL Error for $url: " . $curlError);
        return ['error' => 'API request failed', 'details' => $curlError];
    }

    $responseData = json_decode($response, true);

    if ($httpCode >= 400) {
        error_log("API Error for $url (HTTP $httpCode): " . $response);
        return array_merge(['error' => 'API returned an error', 'http_code' => $httpCode], $responseData ?? []);
    }

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON Decode Error for $url: " . json_last_error_msg());
        return ['error' => 'Failed to decode API response', 'raw_response' => $response];
    }

    return $responseData;
}

?> 