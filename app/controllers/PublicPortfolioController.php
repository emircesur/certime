<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Credential;
use App\Models\User;
use App\Models\SkillTaxonomy;

/**
 * PublicPortfolioController — SEO-friendly public portfolio pages
 */
class PublicPortfolioController extends Controller
{
    /**
     * Public portfolio by slug
     * GET /p/:slug
     */
    public function show($slug)
    {
        $userModel = new User();
        $pdo = \Database::getInstance();

        // Find by slug or by user ID
        $stmt = $pdo->prepare(
            "SELECT * FROM users WHERE portfolio_slug = :slug AND portfolio_public = 1"
        );
        $stmt->execute([':slug' => $slug]);
        $user = $stmt->fetch();

        if (!$user) {
            // Try by numeric ID
            if (is_numeric($slug)) {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id AND portfolio_public = 1");
                $stmt->execute([':id' => (int)$slug]);
                $user = $stmt->fetch();
            }
        }

        if (!$user) {
            http_response_code(404);
            return $this->view('errors/404', ['title' => 'Portfolio Not Found']);
        }

        $credModel = new Credential();
        $credentials = $credModel->findByUser((int)$user['id']);
        $activeCredentials = array_filter($credentials, fn($c) => $c['status'] === 'active');

        // Get skill aggregation
        $skillModel = new SkillTaxonomy();
        $allSkills = [];
        foreach ($activeCredentials as $cred) {
            $skills = $skillModel->getCredentialSkills($cred['credential_uid']);
            foreach ($skills as $s) {
                $allSkills[$s['code']] = $s;
            }
        }

        // Social links
        $socialLinks = !empty($user['social_links']) ? json_decode($user['social_links'], true) : [];

        return $this->view('portfolio/public', [
            'title' => $user['username'] . "'s Portfolio",
            'user' => $user,
            'credentials' => array_values($activeCredentials),
            'skills' => array_values($allSkills),
            'socialLinks' => $socialLinks,
            'theme' => $user['portfolio_theme'] ?? 'default',
            'totalCredentials' => count($activeCredentials),
        ]);
    }

    /**
     * Portfolio settings (authenticated user)
     */
    public function settings()
    {
        $this->requireAuth();
        $pdo = \Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => currentUserId()]);
        $user = $stmt->fetch();

        return $this->view('portfolio/settings', [
            'title' => 'Portfolio Settings',
            'user' => $user,
            'socialLinks' => !empty($user['social_links']) ? json_decode($user['social_links'], true) : [],
            'themes' => ['default', 'dark', 'minimal', 'academic', 'creative'],
        ]);
    }

    /**
     * Save portfolio settings
     */
    public function saveSettings()
    {
        $this->requireAuth();
        $this->requireCsrf();

        $slug = trim($_POST['portfolio_slug'] ?? '');
        $isPublic = !empty($_POST['portfolio_public']);
        $theme = trim($_POST['portfolio_theme'] ?? 'default');

        $socialLinks = [
            'linkedin' => trim($_POST['linkedin'] ?? ''),
            'twitter' => trim($_POST['twitter'] ?? ''),
            'github' => trim($_POST['github'] ?? ''),
            'website' => trim($_POST['website'] ?? ''),
        ];
        $socialLinks = array_filter($socialLinks);

        // Validate slug
        if (!empty($slug)) {
            $slug = preg_replace('/[^a-z0-9\-]/', '', strtolower($slug));
            if (strlen($slug) < 3) {
                flash('error', 'Portfolio slug must be at least 3 characters.');
                $this->redirect('portfolio/settings');
                return;
            }

            // Check uniqueness
            $pdo = \Database::getInstance();
            $check = $pdo->prepare("SELECT id FROM users WHERE portfolio_slug = :slug AND id != :id");
            $check->execute([':slug' => $slug, ':id' => currentUserId()]);
            if ($check->fetch()) {
                flash('error', 'This portfolio URL is already taken.');
                $this->redirect('portfolio/settings');
                return;
            }
        }

        $pdo = \Database::getInstance();
        $stmt = $pdo->prepare(
            "UPDATE users SET portfolio_slug = :slug, portfolio_public = :pub, portfolio_theme = :theme, social_links = :social WHERE id = :id"
        );
        $stmt->execute([
            ':slug' => $slug ?: null,
            ':pub' => $isPublic ? 1 : 0,
            ':theme' => $theme,
            ':social' => json_encode($socialLinks),
            ':id' => currentUserId(),
        ]);

        flash('success', 'Portfolio settings saved.');
        $this->redirect('portfolio/settings');
    }
}
