<?php

namespace App\Controllers;

use App\Core\Controller;

class BadgeBuilderController extends Controller
{
    public function __construct()
    {
        $this->requireAuth();
    }

    /**
     * Visual badge builder page
     */
    public function index()
    {
        return $this->view('badge/builder', [
            'title' => 'Visual Badge Builder',
        ]);
    }

    /**
     * Save badge design as SVG
     */
    public function save()
    {
        $this->requireCsrf();

        $svgData = $_POST['svg_data'] ?? '';
        $badgeName = trim($_POST['badge_name'] ?? 'My Badge');

        if (empty($svgData)) {
            $this->json(['error' => 'No badge data provided.'], 400);
            return;
        }

        $badgeDir = DATA_PATH . '/badges/' . currentUserId();
        @mkdir($badgeDir, 0700, true);

        $filename = secureUid('badge_') . '.svg';
        $filePath = $badgeDir . '/' . $filename;

        if (file_put_contents($filePath, $svgData)) {
            \Database::audit('badge.create', "User " . currentUserId() . " created badge: {$badgeName}");
            $this->json([
                'success' => true,
                'message' => 'Badge saved successfully!',
                'filename' => $filename,
            ]);
        } else {
            $this->json(['error' => 'Failed to save badge.'], 500);
        }
    }

    /**
     * List saved badges
     */
    public function list()
    {
        $badgeDir = DATA_PATH . '/badges/' . currentUserId();
        $badges = [];

        if (is_dir($badgeDir)) {
            foreach (glob($badgeDir . '/*.svg') as $file) {
                $badges[] = [
                    'filename' => basename($file),
                    'created' => date('Y-m-d H:i:s', filemtime($file)),
                    'size' => filesize($file),
                ];
            }
        }

        $this->json(['badges' => $badges]);
    }
}
