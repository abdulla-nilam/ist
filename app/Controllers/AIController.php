<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\BookService;
use App\Services\AIService;

class AIController
{

    /**
     * Handle the POST request to generate a summary for a book.
     *
     * Expects a JSON payload with a "book_id" key and returns a merged JSON object
     * containing the book data along with the generated summary.
     * @throws \JsonException
     */
    public function generateSummary(): void
    {
        $data = $this->getJsonInput();

        if (!isset($data['book_id'])) {
            $this->jsonResponse(['error' => 'book_id is required'], 400);
        }

        $bookId = (int)$data['book_id'];
        $book = BookService::find($bookId);
        if (!$book) {
            $this->jsonResponse(['error' => 'Book not found'], 404);
        }

        $summary = AIService::generateSummary($book);
        // Merge the book data with the generated summary into a single object.
        $result = array_merge($book, compact('summary'));
        $this->jsonResponse($result);
    }

    /**
     * Sends a JSON response with the specified data and HTTP status code.
     *
     * @param  array  $data
     * @param  int  $statusCode
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
     * Reads and decodes JSON input from php://input.
     *
     * @return array
     * @throws \JsonException
     */
    private function getJsonInput(): array
    {
        try {
            $input = file_get_contents('php://input');

            return json_decode($input, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $this->jsonResponse(['error' => 'Invalid JSON input'], 400);
        }
    }

}
