<?php

namespace App\Core;

class Controller
{
    /**
     * Render a view with data
     */
    public function view(string $view, array $data = [])
    {
        extract($data);
        $viewFile = APP_PATH . '/views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            http_response_code(500);
            die("View not found: {$view}");
        }
        require $viewFile;
    }

    /**
     * Redirect to a path (automatically prepends base path)
     */
    public function redirect(string $path = '')
    {
        header("Location: " . url($path));
        exit();
    }

    /**
     * Send a JSON response
     */
    public function json(array $data, int $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit();
    }

    /**
     * Require authentication - redirect to login if not logged in
     */
    protected function requireAuth()
    {
        if (!isLoggedIn()) {
            flash('error', 'Please log in to continue.');
            $this->redirect('login');
        }
    }

    /**
     * Require admin role
     */
    protected function requireAdmin()
    {
        $this->requireAuth();
        if (!isAdmin()) {
            http_response_code(403);
            $this->view('errors/403', ['title' => 'Forbidden']);
            exit();
        }
    }

    /**
     * Require staff (moderator or admin)
     */
    protected function requireStaff()
    {
        $this->requireAuth();
        if (!isStaff()) {
            http_response_code(403);
            $this->view('errors/403', ['title' => 'Forbidden']);
            exit();
        }
    }

    /**
     * Verify CSRF token on POST requests
     */
    protected function requireCsrf()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !verifyCsrf()) {
            // For AJAX/JSON requests, return JSON error
            if (\App\Core\Request::isAjax() || \App\Core\Request::isJson()) {
                $this->json(['error' => 'Invalid CSRF token'], 403);
            }
            // For form submissions, redirect back with flash
            flash('error', 'Session expired. Please try again.');
            $referer = $_SERVER['HTTP_REFERER'] ?? '';
            if (!empty($referer)) {
                header('Location: ' . $referer);
                exit();
            }
            $this->redirect('');
        }
    }
}
