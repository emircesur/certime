<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Credential;

/**
 * SocialController — Multi-platform social sharing & share tracking
 */
class SocialController extends Controller
{
    /**
     * Share a credential on social media
     * GET /share/:uid/:platform
     */
    public function share($uid, $platform = 'linkedin')
    {
        $credModel = new Credential();
        $cred = $credModel->findByUid($uid);

        if (!$cred) {
            flash('error', 'Credential not found.');
            $this->redirect('');
            return;
        }

        // Increment share counter
        $pdo = \Database::getInstance();
        $stmt = $pdo->prepare("UPDATE credentials SET share_count = COALESCE(share_count, 0) + 1 WHERE credential_uid = :uid");
        $stmt->execute([':uid' => $uid]);

        $credUrl = absUrl('credential/' . $uid);
        $text = "I earned the '{$cred['course_name']}' credential from {$cred['issuer_name']}! Verify it here:";
        $encodedUrl = urlencode($credUrl);
        $encodedText = urlencode($text);

        $shareUrl = match($platform) {
            'linkedin' => "https://www.linkedin.com/sharing/share-offsite/?url={$encodedUrl}",
            'twitter', 'x' => "https://twitter.com/intent/tweet?text={$encodedText}&url={$encodedUrl}",
            'facebook' => "https://www.facebook.com/sharer/sharer.php?u={$encodedUrl}",
            'email' => "mailto:?subject=" . urlencode("Check out my credential: {$cred['course_name']}") . "&body={$encodedText}%20{$encodedUrl}",
            'whatsapp' => "https://wa.me/?text={$encodedText}%20{$encodedUrl}",
            'telegram' => "https://t.me/share/url?url={$encodedUrl}&text={$encodedText}",
            'embed' => null, // handle separately
            default => null,
        };

        if ($platform === 'embed') {
            return $this->embedCode($uid, $cred);
        }

        if (!$shareUrl) {
            flash('error', 'Unknown platform.');
            $this->redirect('credential/' . $uid);
            return;
        }

        header('Location: ' . $shareUrl);
        exit();
    }

    /**
     * Show embed code for a credential
     */
    private function embedCode($uid, $cred)
    {
        $embedUrl = absUrl('credential/' . $uid . '/badge');
        $verifyUrl = absUrl('credential/' . $uid);

        return $this->view('credential/embed', [
            'title' => 'Embed Credential',
            'credential' => $cred,
            'embedUrl' => $embedUrl,
            'verifyUrl' => $verifyUrl,
            'iframeCode' => '<iframe src="' . $embedUrl . '" width="300" height="300" frameborder="0"></iframe>',
            'linkCode' => '<a href="' . $verifyUrl . '"><img src="' . absUrl('credential/' . $uid . '/badge-image') . '" alt="' . e($cred['course_name']) . '" width="200"></a>',
        ]);
    }

    /**
     * Share page — shows all sharing options
     * GET /credential/:uid/share
     */
    public function sharePage($uid)
    {
        $credModel = new Credential();
        $cred = $credModel->findByUid($uid);

        if (!$cred) {
            flash('error', 'Credential not found.');
            $this->redirect('');
            return;
        }

        $credUrl = absUrl('credential/' . $uid);

        return $this->view('credential/share', [
            'title' => 'Share Credential',
            'credential' => $cred,
            'credUrl' => $credUrl,
            'shareCount' => $cred['share_count'] ?? 0,
            'platforms' => [
                ['id' => 'linkedin', 'name' => 'LinkedIn', 'icon' => 'work', 'color' => '#0077b5'],
                ['id' => 'twitter', 'name' => 'X (Twitter)', 'icon' => 'tag', 'color' => '#1da1f2'],
                ['id' => 'facebook', 'name' => 'Facebook', 'icon' => 'thumb_up', 'color' => '#4267B2'],
                ['id' => 'whatsapp', 'name' => 'WhatsApp', 'icon' => 'chat', 'color' => '#25D366'],
                ['id' => 'telegram', 'name' => 'Telegram', 'icon' => 'send', 'color' => '#0088cc'],
                ['id' => 'email', 'name' => 'Email', 'icon' => 'mail', 'color' => '#666'],
                ['id' => 'embed', 'name' => 'Embed Code', 'icon' => 'code', 'color' => '#333'],
            ],
        ]);
    }

    /**
     * Open Graph metadata endpoint for rich social previews
     * GET /og/:uid
     */
    public function openGraph($uid)
    {
        $credModel = new Credential();
        $cred = $credModel->findByUid($uid);

        if (!$cred) {
            $this->json(['error' => 'Not found'], 404);
            return;
        }

        return $this->view('credential/opengraph', [
            'title' => $cred['course_name'] . ' — CertiMe Credential',
            'credential' => $cred,
            'credUrl' => absUrl('credential/' . $uid),
            'imageUrl' => absUrl('credential/' . $uid . '/badge-image'),
        ]);
    }
}
