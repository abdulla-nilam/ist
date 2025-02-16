<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\BookService;
use App\Middleware\AuthMiddleware;

class BookController
{

    /**
     * GET /api/books
     * @throws \JsonException
     */
    public function index(): void
    {
        $books = BookService::getAll();
        $this->jsonResponse($books);
    }

    /**
     * GET /api/books/{id}
     * @throws \JsonException
     */
    public function show(int $id): void
    {
        $book = BookService::find($id);
        if ($book) {
            $this->jsonResponse($book);
        } else {
            $this->jsonResponse(['error' => 'Book not found'], 404);
        }
    }

    /**
     * POST /api/books
     * @throws \JsonException
     */
    public function store(): void
    {
        $data = $this->getJsonInput();

        if (!isset($data['title'], $data['author'], $data['published_year'])) {
            $this->jsonResponse(
                ['error' => 'Invalid input: title, author and published_year are required'],
                400
            );
        }

        $title = trim((string)$data['title']);
        $author = trim((string)$data['author']);
        $published_year = filter_var($data['published_year'], FILTER_VALIDATE_INT);

        if ($title === '' || $author === '') {
            $this->jsonResponse(['error' => 'Title and author cannot be empty'], 400);
        }

        if ($published_year === false) {
            $this->jsonResponse(['error' => 'Published year must be an integer'], 400);
        }

        $auth = AuthMiddleware::handle();
        $created_by = $auth['username'] ?? 'system';

        $book = BookService::create($title, $author, $published_year, $created_by);
        $this->jsonResponse($book);
    }

    /**
     * PUT /api/books/{id}
     * Allows updating at least one field (title, author, or published_year).
     * @throws \JsonException
     */
    public function update(int $id): void
    {
        $data = $this->getJsonInput();

        if (!isset($data['title']) && !isset($data['author']) && !isset($data['published_year'])) {
            $this->jsonResponse(
                ['error' => 'At least one field (title, author, published_year) must be provided for update'],
                400
            );
        }

        $auth = AuthMiddleware::handle();
        $updated_by = $auth['username'] ?? 'system';

        // Fetch the existing record.
        $existingBook = BookService::find($id);
        if (!$existingBook) {
            $this->jsonResponse(['error' => 'Book not found'], 404);
        }

        // Use new value if provided; otherwise, use existing.
        $title = isset($data['title']) ? trim((string)$data['title']) : $existingBook['title'];
        $author = isset($data['author']) ? trim((string)$data['author']) : $existingBook['author'];

        if (isset($data['title']) && $title === '') {
            $this->jsonResponse(['error' => 'Title cannot be empty'], 400);
        }
        if (isset($data['author']) && $author === '') {
            $this->jsonResponse(['error' => 'Author cannot be empty'], 400);
        }

        if (isset($data['published_year'])) {
            $published_year = filter_var($data['published_year'], FILTER_VALIDATE_INT);
            if ($published_year === false) {
                $this->jsonResponse(['error' => 'Published year must be an integer'], 400);
            }
        } else {
            $published_year = $existingBook['published_year'];
        }

        $updatedBook = BookService::update($id, $title, $author, $published_year, $updated_by);
        $this->jsonResponse($updatedBook);
    }

    /**
     * DELETE /api/books/{id}
     * @throws \JsonException
     */
    public function destroy(int $id): void
    {
        $existingBook = BookService::find($id);
        if (!$existingBook) {
            $this->jsonResponse(['error' => 'Book not found'], 404);
        }

        $deleted = BookService::delete($id);
        if ($deleted) {
            $this->jsonResponse(['message' => 'Book deleted successfully']);
        } else {
            $this->jsonResponse(['error' => 'Book not found'], 404);
        }
    }

    /**
     * Helper to send a JSON response.
     * @throws \JsonException
     */
    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_THROW_ON_ERROR);
        exit;
    }

    /**
     * Helper to retrieve and decode JSON input.
     *
     * @return array
     * @throws \JsonException
     */
    private function getJsonInput(): array
    {
        try {
            return json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $this->jsonResponse(['error' => 'Invalid JSON input'], 400);
        }
    }
}
