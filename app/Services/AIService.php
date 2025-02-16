<?php
namespace App\Services;

class AIService
{
    // Generates a summary
    public static function generateSummary(array $book): string
    {
        $config = require __DIR__ . '/../../config/config.php';
        $googleGeminiApiKey = $config['google_gemini_api_key'] ?? '';

        if (!$googleGeminiApiKey) {
            return "Google Gemini API key not configured.";
        }

        $prompt = "Generate a concise and engaging summary for the following book details:\n" .
            "Title: {$book['title']}\n" .
            "Author: {$book['author']}\n" .
            "Published Year: {$book['published_year']}\n" ;

        // API endpoint
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $googleGeminiApiKey;

        // API payload
        $data = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt]
                    ]
                ]
            ]
        ];

        // Initialize cURL
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            $errorMsg = curl_error($ch);
            curl_close($ch);
            return "Error calling Google Gemini API: " . $errorMsg;
        }

        // HTTP errors
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpStatus < 200 || $httpStatus >= 300) {
            return "Error calling Google Gemini API: HTTP status {$httpStatus}";
        }

        $result = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return "Error decoding response: " . json_last_error_msg();
        }

        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            return trim($result['candidates'][0]['content']['parts'][0]['text']);
        }

        return "Could not generate summary.";
    }
}
