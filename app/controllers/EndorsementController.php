<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Endorsement;
use App\Models\Credential;

class EndorsementController extends Controller
{
    protected Endorsement $endorsementModel;

    public function __construct()
    {
        $this->endorsementModel = new Endorsement();
    }

    /**
     * Create an endorsement for a credential
     */
    public function create($uid)
    {
        $this->requireCsrf();
        
        $credentialModel = new Credential();
        $credential = $credentialModel->findByUid($uid);
        
        if (!$credential) {
            flash('error', 'Credential not found.');
            $this->redirect('');
            return;
        }

        $name = trim($_POST['endorser_name'] ?? '');
        $email = trim($_POST['endorser_email'] ?? '');
        $org = trim($_POST['endorser_org'] ?? '');
        $title = trim($_POST['endorser_title'] ?? '');
        $comment = trim($_POST['comment'] ?? '');

        if (empty($name) || empty($comment)) {
            flash('error', 'Name and comment are required.');
            $this->redirect('credential/' . $uid);
            return;
        }

        $id = $this->endorsementModel->create(
            $credential['id'], $name, $email ?: '', $org, $title, $comment
        );

        if ($id) {
            flash('success', 'Endorsement submitted for review.');
        } else {
            flash('error', 'Failed to create endorsement.');
        }
        $this->redirect('credential/' . $uid);
    }

    /**
     * Approve an endorsement (admin/moderator only)
     */
    public function approve($id)
    {
        $this->requireStaff();
        $this->requireCsrf();
        
        $id = (int)$id;
        $endorsement = $this->endorsementModel->findById($id);
        
        if (!$endorsement) {
            $this->json(['error' => 'Endorsement not found'], 404);
        }

        // Optionally sign the endorsement
        $signature = '';
        try {
            $keyPath = KEYS_PATH . '/issuer.key';
            if (file_exists($keyPath)) {
                $privateKey = sodium_hex2bin(trim(file_get_contents($keyPath)));
                $data = $endorsement['endorser_name'] . ':' . $endorsement['comment'] . ':' . $endorsement['credential_id'];
                $sig = sodium_crypto_sign_detached($data, $privateKey);
                $signature = base64_encode($sig);
            }
        } catch (\Exception $e) {
            // Continue without signature
        }

        if (!empty($signature)) {
            $this->endorsementModel->sign($id, $signature);
        } else {
            $this->endorsementModel->updateStatus($id, 'approved');
        }

        $this->json(['success' => true, 'message' => 'Endorsement approved.']);
    }

    /**
     * Reject an endorsement
     */
    public function reject($id)
    {
        $this->requireStaff();
        $this->requireCsrf();
        
        $id = (int)$id;
        
        if ($this->endorsementModel->updateStatus($id, 'rejected')) {
            $this->json(['success' => true, 'message' => 'Endorsement rejected.']);
        } else {
            $this->json(['error' => 'Failed to reject endorsement'], 500);
        }
    }
}
