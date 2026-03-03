<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class AuthController extends Controller
{
    protected User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function register()
    {
        if (isLoggedIn()) {
            $this->redirect('portfolio');
        }
        return $this->view('auth/register', [
            'title' => 'Register',
            'error' => flash('error'),
            'success' => flash('success')
        ]);
    }

    public function handleRegister()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('register');
        }

        $this->requireCsrf();

        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';
        $fullName = trim($_POST['full_name'] ?? '');

        // Validation
        $errors = [];
        if (empty($username) || strlen($username) < 3) {
            $errors[] = 'Username must be at least 3 characters.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }
        if ($password !== $passwordConfirm) {
            $errors[] = 'Passwords do not match.';
        }
        if ($this->userModel->findByEmail($email)) {
            $errors[] = 'Email already in use.';
        }
        if ($this->userModel->findByUsername($username)) {
            $errors[] = 'Username already taken.';
        }

        if (!empty($errors)) {
            return $this->view('auth/register', [
                'title' => 'Register',
                'error' => implode(' ', $errors),
                'old' => ['username' => $username, 'email' => $email, 'full_name' => $fullName]
            ]);
        }

        $userId = $this->userModel->create($username, $email, $password);

        if ($userId) {
            if (!empty($fullName)) {
                $this->userModel->updateProfile($userId, ['full_name' => $fullName]);
            }
            
            // Log the user in
            session_regenerate_id(true);
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_username'] = $username;
            $_SESSION['user_role'] = 'student';

            flash('success', 'Welcome to CertiMe! Your account has been created.');
            $this->redirect('portfolio');
        } else {
            return $this->view('auth/register', [
                'title' => 'Register',
                'error' => 'Registration failed. Please try again.',
                'old' => ['username' => $username, 'email' => $email, 'full_name' => $fullName]
            ]);
        }
    }

    public function login()
    {
        if (isLoggedIn()) {
            $this->redirect('portfolio');
        }
        return $this->view('auth/login', [
            'title' => 'Login',
            'error' => flash('error'),
            'success' => flash('success')
        ]);
    }

    public function handleLogin()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('login');
        }

        $this->requireCsrf();

        $login = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($login) || empty($password)) {
            return $this->view('auth/login', [
                'title' => 'Login',
                'error' => 'Username/email and password are required.',
                'old' => ['username' => $login]
            ]);
        }

        // Try finding by email first, then by username, then email as fallback
        $user = null;
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $user = $this->userModel->findByEmail($login);
        }
        if (!$user) {
            $user = $this->userModel->findByUsername($login);
        }
        if (!$user) {
            // Fallback: try as email even if filter_var rejected it (e.g. admin@local)
            $user = $this->userModel->findByEmail($login);
        }

        if ($user && $this->userModel->verifyPassword($password, $user['password_hash'])) {
            // Check if user is active
            if (isset($user['is_active']) && !$user['is_active']) {
                return $this->view('auth/login', [
                    'title' => 'Login',
                    'error' => 'Your account has been deactivated. Please contact an administrator.',
                    'old' => ['username' => $login]
                ]);
            }

            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];

            $this->userModel->updateLastLogin($user['id']);
            \Database::audit('user.login', "User logged in: {$user['username']}");

            // Redirect based on role
            if ($user['role'] === 'admin' || $user['role'] === 'moderator') {
                $this->redirect('admin');
            } else {
                $this->redirect('portfolio');
            }
        } else {
            return $this->view('auth/login', [
                'title' => 'Login',
                'error' => 'Invalid username/email or password.',
                'old' => ['username' => $login]
            ]);
        }
    }

    public function logout()
    {
        if (isLoggedIn()) {
            \Database::audit('user.logout', "User logged out: " . ($_SESSION['user_username'] ?? 'unknown'));
        }
        session_destroy();
        $this->redirect('');
    }
}
