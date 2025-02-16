<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Utils\JWT;

class AuthMiddleware
{
    /**
     * Validates the Authorization header and returns the decoded token payload
     *
     * @return array|null JWT payload on success, or an error array.
     */
    public static function handle(): ?array
    {
        $headers = getallheaders();

        if (!isset($headers['Authorization'])) {
            return ['error' => 'Authorization header missing'];
        }

        // Expect header in the form "Bearer token"
        if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
            $token = $matches[1];
        } else {
            return ['error' => 'Invalid authorization header format'];
        }

        $config = require __DIR__ . '/../../config/config.php';

        try {
            return JWT::validate($token, $config['jwt_secret']);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
