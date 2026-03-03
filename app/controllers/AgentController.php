<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Lib\Agent;

class AgentController extends Controller
{
    public function __construct()
    {
        if (!isLoggedIn()) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized']);
            exit();
        }
    }

    public function chat()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Method Not Allowed'], 405);
        }

        // Verify CSRF from header
        $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['_csrf_token'] ?? '';
        if (empty($csrfToken) || !hash_equals($_SESSION['_csrf_token'] ?? '', $csrfToken)) {
            $this->json(['error' => 'Invalid CSRF token'], 403);
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $message = trim($data['message'] ?? '');

        if (empty($message)) {
            $this->json(['error' => 'Message is required'], 400);
        }

        if (strlen($message) > 2000) {
            $this->json(['error' => 'Message too long'], 400);
        }

        // Release session lock so UI stays responsive
        session_write_close();

        $agent = new Agent(currentUserId());
        $response = $agent->chat($message);
        
        header('Content-Type: application/json');
        echo json_encode(['reply' => $response]);
        exit;
    }
}
