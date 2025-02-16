<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\UserService;
use App\Utils\JWT;

class AuthController
{

    /**
     * POST /api/register
     * Registers a new user.
     * @throws \JsonException
     */
    public function register(): void
    {
        $data = $this->getJsonInput();
        $username = $this->getRequiredField($data, 'username');
        $password = $this->getRequiredField($data, 'password');

        $result = UserService::register($username, $password);
        header('Content-Type: application/json');
        if ($result) {
            echo json_encode(['message' => 'User registered successfully'], JSON_THROW_ON_ERROR);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Username already exists'], JSON_THROW_ON_ERROR);
        }
    }

    /**
     * POST /api/login
     * Authenticates a user and returns a JWT token.
     * @throws \JsonException
     */
    public function login(): void
    {
        $data = $this->getJsonInput();
        $username = $this->getRequiredField($data, 'username');
        $password = $this->getRequiredField($data, 'password');

        if (UserService::login($username, $password)) {
            $config = require __DIR__.'/../../config/config.php';
            $token = JWT::generate(compact('username'), $config['jwt_secret'], $config['jwt_expiration']);
            header('Content-Type: application/json');
            echo json_encode(compact('token'), JSON_THROW_ON_ERROR);
        } else {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid credentials'], JSON_THROW_ON_ERROR);
        }
    }

    /**
     * Reads and decodes the JSON input from php://input.
     * Exits with an error response if JSON is invalid.
     *
     * @return array The decoded JSON input.
     * @throws \JsonException
     */
    private function getJsonInput(): array
    {
        try {
            return json_decode(file_get_contents("php://input"), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid JSON input'], JSON_THROW_ON_ERROR);
            exit;
        }
    }

    /**
     * Retrieves a required field from the input data, ensuring it exists and is non-empty.
     * Exits with an error response if the field is missing or empty.
     *
     * @param  array  $data  The input data.
     * @param  string  $fieldName  The required field name.
     *
     * @return string The sanitized field value.
     * @throws \JsonException
     */
    private function getRequiredField(array $data, string $fieldName): string
    {
        if (!isset($data[$fieldName])) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => "{$fieldName} is required"], JSON_THROW_ON_ERROR);
            exit;
        }
        $value = trim((string)$data[$fieldName]);
        if ($value === '') {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => "{$fieldName} cannot be empty"], JSON_THROW_ON_ERROR);
            exit;
        }

        return $value;
    }
}
