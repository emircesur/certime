<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Credential;
use App\Lib\MerkleTree;

class TranscriptController extends Controller
{
    protected User $userModel;
    protected Credential $credentialModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->credentialModel = new Credential();
    }

    public function show($userId)
    {
        $userId = (int)$userId;
        
        // Auth check: only the user themselves or staff can view transcripts
        if (!isLoggedIn()) {
            flash('error', 'Please log in to view transcripts.');
            $this->redirect('login');
        }
        
        if (currentUserId() !== $userId && !isStaff()) {
            http_response_code(403);
            return $this->view('errors/403', ['title' => 'Forbidden']);
        }

        $user = $this->userModel->findById($userId);
        if (!$user) {
            return $this->view('errors/404', ['title' => 'User Not Found']);
        }

        $credentials = $this->credentialModel->findByUser($userId);
        
        // Build Merkle tree from credential JSON-LD payloads
        $credentialData = array_map(fn($c) => $c['badge_jsonld'], $credentials);
        $merkleTree = new MerkleTree($credentialData);
        $merkleRoot = $merkleTree->getRoot();

        // Sign the Merkle root
        $signature = MerkleTree::signRoot($merkleRoot);
        $signatureValid = !empty($signature) && MerkleTree::verifyRootSignature($merkleRoot, $signature);

        // Generate proofs for each credential
        $proofs = [];
        foreach ($credentials as $i => $c) {
            $proofs[$c['credential_uid']] = $merkleTree->getProof($i);
        }

        return $this->view('transcript/show', [
            'title' => 'Transcript - ' . e($user['username']),
            'user' => $user,
            'credentials' => $credentials,
            'merkleRoot' => $merkleRoot,
            'signature' => $signature,
            'signatureValid' => $signatureValid,
            'proofs' => $proofs,
        ]);
    }
}
