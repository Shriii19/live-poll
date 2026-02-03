<?php
/**
 * Main Entry Point - Standalone PHP Application
 * Routes all requests to appropriate handlers
 */

require_once __DIR__ . '/../config.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Route definitions
$routes = [
    // Auth routes
    'GET:/' => 'auth/login.php',
    'GET:/login' => 'auth/login.php',
    'POST:/login' => 'api/auth.php',
    'POST:/logout' => 'api/auth.php',
    
    // Poll pages
    'GET:/polls' => 'pages/polls.php',
    'GET:/admin' => 'pages/admin.php',
    
    // API routes
    'GET:/api/polls' => 'api/polls.php',
    'POST:/api/polls' => 'api/polls.php',
    'POST:/api/vote' => 'api/vote.php',
    
    // Admin API routes
    'GET:/api/admin/polls' => 'api/admin.php',
    'POST:/api/admin/release-ip' => 'api/admin.php',
];

// Handle dynamic routes with parameters
$routeKey = "$method:$uri";

// Check for exact match
if (isset($routes[$routeKey])) {
    require __DIR__ . '/' . $routes[$routeKey];
    exit;
}

// Check for pattern matches (API routes with IDs)
if (preg_match('#^GET:/api/polls/(\d+)$#', $uri, $matches)) {
    $_GET['id'] = $matches[1];
    $_GET['action'] = 'get';
    require __DIR__ . '/api/polls.php';
    exit;
}

if (preg_match('#^GET:/api/polls/(\d+)/results$#', $uri, $matches)) {
    $_GET['id'] = $matches[1];
    $_GET['action'] = 'results';
    require __DIR__ . '/api/polls.php';
    exit;
}

if (preg_match('#^POST:/api/polls/(\d+)/toggle$#', $uri, $matches)) {
    $_POST['id'] = $matches[1];
    $_POST['action'] = 'toggle';
    require __DIR__ . '/api/polls.php';
    exit;
}

if (preg_match('#^GET:/api/admin/polls/(\d+)/voters$#', $uri, $matches)) {
    $_GET['poll_id'] = $matches[1];
    $_GET['action'] = 'voters';
    require __DIR__ . '/api/admin.php';
    exit;
}

if (preg_match('#^GET:/api/admin/polls/(\d+)/voters-history$#', $uri, $matches)) {
    $_GET['poll_id'] = $matches[1];
    $_GET['action'] = 'voters-history';
    require __DIR__ . '/api/admin.php';
    exit;
}

if (preg_match('#^GET:/api/admin/polls/(\d+)/history/(.+)$#', $uri, $matches)) {
    $_GET['poll_id'] = $matches[1];
    $_GET['ip'] = $matches[2];
    $_GET['action'] = 'history';
    require __DIR__ . '/api/admin.php';
    exit;
}

// 404 Not Found
http_response_code(404);
echo "404 Not Found";
