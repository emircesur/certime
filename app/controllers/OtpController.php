<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\OtpClaim;
use App\Models\Credential;
use App\Models\User;

/**
 * OtpController — Frictionless One-Time-Password badge claiming
 */
class OtpController extends Controller
{
    /**
     * Show claim form (public)
     */
    public function claimForm()
    {
        return $this->view('credential/otp_claim', [
            'title' => 'Claim Your Badge',
        ]);
    }

    /**
     * Handle OTP verification
     */
    public function verify()
    {
        $email = trim($_POST['email'] ?? '');
        $code = trim($_POST['code'] ?? '');

        if (empty($email) || empty($code)) {
            flash('error', 'Email and verification code are required.');
            $this->redirect('claim');
            return;
        }

        $otpModel = new OtpClaim();
        $claim = $otpModel->verify($email, $code);

        if (!$claim) {
            flash('error', 'Invalid or expired verification code.');
            $this->redirect('claim');
            return;
        }

        // Mark as claimed
        $otpModel->markClaimed((int)$claim['id']);

        // Auto-login or register the user
        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if (!$user) {
            // Auto-register with a random password
            $password = bin2hex(random_bytes(16));
            $userId = $userModel->create($email, $email, $password);
            $user = $userModel->findById($userId);
        }

        // Assign credential to user
        $credModel = new Credential();
        $cred = $credModel->findByUid($claim['credential_uid']);
        if ($cred && !$cred['user_id']) {
            $pdo = \Database::getInstance();
            $stmt = $pdo->prepare("UPDATE credentials SET user_id = :uid WHERE credential_uid = :cuid");
            $stmt->execute([':uid' => $user['id'], ':cuid' => $claim['credential_uid']]);
        }

        // Log them in
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_username'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];

        \Database::audit('otp.claimed', "Badge {$claim['credential_uid']} claimed by {$email}");
        flash('success', 'Badge claimed successfully! Welcome to your portfolio.');
        $this->redirect('portfolio');
    }

    /**
     * Admin: Generate OTP for a credential
     */
    public function generate()
    {
        $this->requireStaff();
        $this->requireCsrf();

        $credentialUid = trim($_POST['credential_uid'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($credentialUid) || empty($email)) {
            flash('error', 'Credential UID and recipient email are required.');
            $this->redirect('admin/credentials');
            return;
        }

        $otpModel = new OtpClaim();
        $code = $otpModel->create($credentialUid, $email);

        if ($code) {
            flash('success', "OTP code generated for {$email}: {$code} (valid 24 hours)");
        } else {
            flash('error', 'Failed to generate OTP code.');
        }

        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if (!empty($referer)) {
            header('Location: ' . $referer);
            exit();
        }
        $this->redirect('admin/credentials');
    }

    /**
     * Admin: View pending OTP claims
     */
    public function pending()
    {
        $this->requireStaff();
        $otpModel = new OtpClaim();
        $claims = $otpModel->getPending();

        return $this->view('admin/super/otp_pending', [
            'title' => 'Pending OTP Claims',
            'claims' => $claims,
        ]);
    }
}
