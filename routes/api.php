<?php
use App\Controllers\AuthController;

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Define public routes.
if ($uri === '/api/register' && $method === 'POST') {
    (new AuthController())->register();
    exit;
}
if ($uri === '/api/login' && $method === 'POST') {
    (new AuthController())->login();
    exit;
}



// If no route matched
http_response_code(404);
echo json_encode(['error' => 'Endpoint not found']);
