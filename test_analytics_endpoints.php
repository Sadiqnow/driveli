<?php

// Test script for Driver Analytics API endpoints
// Run with: php test_analytics_endpoints.php

$baseUrl = 'http://127.0.0.1:8000/api';

// Test results
$results = [
    'login' => false,
    'endpoints' => []
];

// Function to make HTTP requests
function makeRequest($url, $method = 'GET', $headers = [], $data = null) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);

    curl_close($ch);

    return [
        'response' => $response,
        'http_code' => $httpCode,
        'error' => $error
    ];
}

// Test admin login
echo "Testing admin login...\n";
$loginData = [
    'email' => 'admin@drivelink.com', // Assuming this admin exists
    'password' => 'password123'
];

$loginResult = makeRequest("$baseUrl/admin/login", 'POST', ['Content-Type: application/json'], $loginData);

if ($loginResult['http_code'] === 200) {
    $loginResponse = json_decode($loginResult['response'], true);
    if (isset($loginResponse['data']['token'])) {
        $token = $loginResponse['data']['token'];
        $results['login'] = true;
        echo "✓ Login successful, token obtained\n";
    } else {
        echo "✗ Login response missing token\n";
        print_r($loginResponse);
        exit(1);
    }
} else {
    echo "✗ Login failed with code {$loginResult['http_code']}\n";
    echo "Response: " . $loginResult['response'] . "\n";
    exit(1);
}

// Test endpoints
$endpoints = [
    'stats' => '/admin/drivers/stats',
    'recent' => '/admin/drivers/recent',
    'verification-stats' => '/admin/drivers/verification-stats',
    'kyc-stats' => '/admin/drivers/kyc-stats',
    'activity' => '/admin/drivers/activity',
    'performance' => '/admin/drivers/performance',
    'demographics' => '/admin/drivers/demographics',
    'retention' => '/admin/drivers/retention',
    'engagement' => '/admin/drivers/engagement',
    'satisfaction' => '/admin/drivers/satisfaction'
];

$headers = [
    'Authorization: Bearer ' . $token,
    'Accept: application/json'
];

foreach ($endpoints as $name => $endpoint) {
    echo "Testing $name endpoint...\n";

    $result = makeRequest("$baseUrl$endpoint", 'GET', $headers);

    $results['endpoints'][$name] = [
        'http_code' => $result['http_code'],
        'success' => false,
        'response_size' => strlen($result['response']),
        'has_json' => false,
        'has_data' => false,
        'error' => $result['error']
    ];

    if ($result['http_code'] === 200) {
        $json = json_decode($result['response'], true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $results['endpoints'][$name]['has_json'] = true;
            if (isset($json['success']) && $json['success'] === true && isset($json['data'])) {
                $results['endpoints'][$name]['success'] = true;
                $results['endpoints'][$name]['has_data'] = true;
                echo "✓ $name: Success - Valid JSON response with data\n";
            } else {
                echo "⚠ $name: HTTP 200 but invalid response structure\n";
                echo "Response: " . substr($result['response'], 0, 200) . "...\n";
            }
        } else {
            echo "✗ $name: HTTP 200 but invalid JSON\n";
            echo "Response: " . substr($result['response'], 0, 200) . "...\n";
        }
    } else {
        echo "✗ $name: HTTP {$result['http_code']}\n";
        if ($result['error']) {
            echo "Error: {$result['error']}\n";
        }
        echo "Response: " . substr($result['response'], 0, 200) . "...\n";
    }
}

// Test unauthorized access (without token)
echo "\nTesting unauthorized access...\n";
foreach (array_slice($endpoints, 0, 2) as $name => $endpoint) { // Test first 2 endpoints
    $result = makeRequest("$baseUrl$endpoint", 'GET', ['Accept: application/json']);
    if ($result['http_code'] === 401) {
        echo "✓ $name: Correctly returns 401 for unauthorized access\n";
    } else {
        echo "⚠ $name: Expected 401, got {$result['http_code']}\n";
    }
}

// Performance test
echo "\nTesting performance (multiple requests)...\n";
$performanceResults = [];
foreach (array_slice($endpoints, 0, 3) as $name => $endpoint) { // Test first 3 endpoints
    $start = microtime(true);
    for ($i = 0; $i < 5; $i++) {
        makeRequest("$baseUrl$endpoint", 'GET', $headers);
    }
    $end = microtime(true);
    $avgTime = ($end - $start) / 5;
    $performanceResults[$name] = $avgTime;
    echo "✓ $name: Average response time: " . number_format($avgTime, 3) . " seconds\n";
}

// Summary
echo "\n" . str_repeat("=", 50) . "\n";
echo "TEST SUMMARY\n";
echo str_repeat("=", 50) . "\n";

echo "Login: " . ($results['login'] ? "✓ PASS" : "✗ FAIL") . "\n";

$passed = 0;
$total = count($results['endpoints']);
foreach ($results['endpoints'] as $name => $result) {
    $status = $result['success'] ? "✓ PASS" : "✗ FAIL";
    echo "$name: $status (HTTP {$result['http_code']})\n";
    if ($result['success']) $passed++;
}

echo "\nOverall: $passed/$total endpoints passed\n";

if ($passed === $total && $results['login']) {
    echo "🎉 ALL TESTS PASSED!\n";
    exit(0);
} else {
    echo "❌ SOME TESTS FAILED\n";
    exit(1);
}
