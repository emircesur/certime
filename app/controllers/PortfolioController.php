<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Credential;

class PortfolioController extends Controller
{
    protected Credential $credentialModel;

    public function __construct()
    {
        $this->requireAuth();
        $this->credentialModel = new Credential();
    }

    public function index()
    {
        $userId = currentUserId();
        $credentials = $this->credentialModel->findByUser($userId);

        // Load user data
        $userModel = new \App\Models\User();
        $user = $userModel->findById($userId);

        // Load portfolio JSON if exists
        $portfolioFile = PORTFOLIOS_PATH . '/' . $userId . '.json';
        $portfolio = file_exists($portfolioFile) 
            ? json_decode(file_get_contents($portfolioFile), true) 
            : null;

        return $this->view('portfolio/index', [
            'title' => 'My Portfolio',
            'credentials' => $credentials,
            'portfolio' => $portfolio,
            'user' => $user,
            'success' => flash('success'),
        ]);
    }

    /**
     * Export portfolio as JSON
     */
    public function export()
    {
        $userId = currentUserId();
        $credentials = $this->credentialModel->findByUser($userId);
        $userModel = new \App\Models\User();
        $user = $userModel->findById($userId);

        $export = [
            '@context' => 'https://www.w3.org/2018/credentials/v1',
            'type' => 'VerifiablePresentation',
            'holder' => [
                'id' => 'mailto:' . $user['email'],
                'name' => $user['full_name'] ?: $user['username'],
            ],
            'verifiableCredential' => array_map(function($c) {
                return json_decode($c['badge_jsonld'], true);
            }, $credentials),
            'exported' => date('c'),
        ];

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="portfolio-' . $user['username'] . '.json"');
        echo json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
