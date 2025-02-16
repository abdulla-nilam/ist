<?php
use App\Controllers\BookController;
use App\Controllers\AuthController;
use App\Controllers\AIController;
use App\Middleware\AuthMiddleware;

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


// For all /api/books routes
// require authentication.
if (str_starts_with($uri, '/api/books')) {
    $auth = AuthMiddleware::handle();
    if (isset($auth['error'])) {
        http_response_code(401);
        echo json_encode($auth);
        exit;
    }
}

// Routes for /api/books
if ($uri === '/api/books') {
    $bookController = new BookController();
    if ($method === 'GET') {
        $bookController->index();
        exit;
    } elseif ($method === 'POST') {
        $bookController->store();
        exit;
    }
}

if ($uri === '/api/books/generate-summary' && $method === 'POST') {
    (new AIController())->generateSummary();
    exit;
}

if (preg_match('#^/api/books/(\d+)$#', $uri, $matches)) {
    $id = $matches[1];
    $bookController = new BookController();
    if ($method === 'GET') {
        $bookController->show($id);
        exit;
    } elseif ($method === 'PUT') {
        $bookController->update($id);
        exit;
    } elseif ($method === 'DELETE') {
        $bookController->destroy($id);
        exit;
    }
}

// If no route matched
http_response_code(404);
echo json_encode(['error' => 'Endpoint not found']);
