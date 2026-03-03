<?php

namespace App\Lib;

use App\Lib\Gemini;
use App\Lib\Tools\GetUserCredentialsTool;

/**
 * AI Agent with Gemini integration and tool-use pattern
 */
class Agent
{
    private int $userId;
    private array $state;
    private string $statePath;
    private ?Gemini $gemini;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
        $this->statePath = DATA_PATH . '/agents/' . $userId . '.json';
        $this->loadState();
        try {
            $this->gemini = new Gemini();
        } catch (\Exception $e) {
            $this->gemini = null;
            error_log("Failed to initialize Gemini client: " . $e->getMessage());
        }
    }

    private function loadState(): void
    {
        if (file_exists($this->statePath) && filesize($this->statePath) > 0) {
            $this->state = json_decode(file_get_contents($this->statePath), true) ?: $this->getDefaultState();
        } else {
            $this->state = $this->getDefaultState();
            $this->saveState();
        }
    }

    private function getDefaultState(): array
    {
        return [
            'system_prompt' => "You are a helpful academic advisor for CertiMe, a digital credentialing platform. Help students understand their skills, recommend new courses, and explain how to share their credentials. Be concise and friendly.",
            'memory' => [],
        ];
    }

    private function saveState(): void
    {
        @mkdir(dirname($this->statePath), 0700, true);
        file_put_contents($this->statePath, json_encode($this->state, JSON_PRETTY_PRINT));
    }

    public function chat(string $message): string
    {
        $this->state['memory'][] = ['role' => 'user', 'content' => $message];
        
        if (!$this->gemini) {
            return "I'm sorry, the AI service is not configured. Please set GEMINI_API_KEY in the .env file.";
        }

        // Tool-use: detect credential-related queries
        $lowerMsg = strtolower($message);
        if (preg_match('/\b(my credentials|my portfolio|my badges|my certificates|what.*earned|my courses)\b/', $lowerMsg)) {
            $tool = new GetUserCredentialsTool();
            $credentialsJson = $tool->execute($this->userId);
            $credentials = json_decode($credentialsJson, true);

            $context = empty($credentials) 
                ? "The user has no credentials yet."
                : "The user has " . count($credentials) . " credentials:\n" . implode("\n", array_map(fn($c) => "- " . $c['course_name'] . " (issued " . ($c['issuance_date'] ?? 'unknown') . ")", $credentials));
            
            $this->state['memory'][] = [
                'role' => 'system',
                'content' => "[CONTEXT] {$context}. Use this information to respond helpfully."
            ];
        }

        // Build conversation in Gemini's expected format
        $conversation = $this->prepareConversation();

        try {
            $response = $this->gemini->generateContent($conversation);
        } catch (\Exception $e) {
            error_log("Gemini API call failed: " . $e->getMessage());
            $response = "I'm having trouble connecting right now. Please try again later.";
        }
        
        $this->state['memory'][] = ['role' => 'assistant', 'content' => $response];
        
        // Trim memory to last 20 messages to prevent unbounded growth
        if (count($this->state['memory']) > 20) {
            $this->state['memory'] = array_slice($this->state['memory'], -20);
        }
        
        $this->saveState();
        return $response;
    }

    /**
     * Prepare conversation in Gemini's native format
     * Gemini expects: [{role: 'user'|'model', parts: [{text: '...'}]}]
     */
    private function prepareConversation(): array
    {
        $conversation = [];
        
        // System prompt as first user message
        $conversation[] = [
            'role' => 'user',
            'parts' => [['text' => $this->state['system_prompt']]]
        ];
        $conversation[] = [
            'role' => 'model',
            'parts' => [['text' => "Understood! I'm CertiMe's academic advisor. How can I help you today?"]]
        ];
        
        foreach ($this->state['memory'] as $msg) {
            $role = ($msg['role'] === 'user') ? 'user' : 'model';
            $conversation[] = [
                'role' => $role,
                'parts' => [['text' => $msg['content']]]
            ];
        }
        
        // Ensure conversation alternates user/model (Gemini requirement)
        $cleaned = [];
        $lastRole = null;
        foreach ($conversation as $msg) {
            if ($msg['role'] === $lastRole) {
                // Merge with previous
                $cleaned[count($cleaned) - 1]['parts'][0]['text'] .= "\n\n" . $msg['parts'][0]['text'];
            } else {
                $cleaned[] = $msg;
                $lastRole = $msg['role'];
            }
        }
        
        return $cleaned;
    }

    /**
     * Clear conversation history
     */
    public function clearHistory(): void
    {
        $this->state['memory'] = [];
        $this->saveState();
    }
}
