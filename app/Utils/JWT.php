<?php
declare(strict_types=1);

namespace App\Utils;

use Exception;

class JWT
{
    /**
     * Generate a JWT token
     *
     * @param array  $payload    The data payload to include in the token.
     * @param string $secret     The secret key used for signing the token.
     * @param int    $expiration The expiration time (in seconds) from now.
     *
     * @return string The generated JWT token.
     *
     * @throws Exception If JSON encoding fails.
     */
    public static function generate(array $payload, #[\SensitiveParameter] string $secret, int $expiration): string
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256'], JSON_THROW_ON_ERROR);
        $payload['exp'] = time() + $expiration;
        $payloadJson = json_encode($payload, JSON_THROW_ON_ERROR);

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payloadJson));

        $signature = hash_hmac('sha256', "{$base64UrlHeader}.{$base64UrlPayload}", $secret, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return "{$base64UrlHeader}.{$base64UrlPayload}.{$base64UrlSignature}";
    }

    /**
     * Validate the given JWT token and return its payload if valid.
     *
     * @param string $token  The JWT token.
     * @param string $secret The secret key used for signing.
     *
     * @return array The decoded payload.
     *
     * @throws Exception If the token format is invalid, the signature doesn't match, or the token has expired.
     */
    public static function validate(#[\SensitiveParameter] string $token, #[\SensitiveParameter] string $secret): array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new Exception('Invalid token format.');
        }
        list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = $parts;

        $expectedSignature = str_replace(
            ['+', '/', '='],
            ['-', '_', ''],
            base64_encode(hash_hmac('sha256', "{$base64UrlHeader}.{$base64UrlPayload}", $secret, true))
        );

        if (!hash_equals($base64UrlSignature, $expectedSignature)) {
            throw new Exception('Invalid token signature.');
        }

        $payloadJson = base64_decode(str_replace(['-', '_'], ['+', '/'], $base64UrlPayload), true);
        if ($payloadJson === false) {
            throw new Exception('Invalid payload encoding.');
        }

        $decodedPayload = json_decode($payloadJson, true, 512, JSON_THROW_ON_ERROR);

        if (!isset($decodedPayload['exp']) || $decodedPayload['exp'] < time()) {
            throw new Exception('Token has expired.');
        }

        return $decodedPayload;
    }
}
