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
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            return "Error calling Google Gemini API: " . $error_msg;
        }
        curl_close($ch);

        $result = json_decode($response, true);

        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            return trim($result['candidates'][0]['content']['parts'][0]['text']);
        }

        return "Could not generate summary.";
    }
}
