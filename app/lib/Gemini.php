<?php

namespace App\Lib;

class Gemini
{
    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = $_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY') ?: '';
        if (empty($this->apiKey)) {
            throw new \Exception('GEMINI_API_KEY is not set in .env file.');
        }
        $this->model = $_ENV['GEMINI_MODEL'] ?? getenv('GEMINI_MODEL') ?: 'gemini-pro';
    }

    /**
     * Send a conversation to Gemini and get a response
     * Expects messages in Gemini format: [{role: 'user'|'model', parts: [{text: '...'}]}]
     */
    public function generateContent(array $contents): string
    {
        $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

        $body = ['contents' => $contents];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception("API request failed: " . $error);
        }
        curl_close($ch);

        $result = json_decode($response, true);
        
        if ($httpCode !== 200) {
            $errorMsg = $result['error']['message'] ?? 'Unknown API error';
            throw new \Exception("Gemini API error ({$httpCode}): {$errorMsg}");
        }

        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            return $result['candidates'][0]['content']['parts'][0]['text'];
        }
        
        error_log("Gemini API unexpected response: " . $response);
        return "Sorry, I could not process your request at the moment.";
    }
}
