<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\UploadedCredential;

class UploadController extends Controller
{
    protected UploadedCredential $model;

    public function __construct()
    {
        $this->requireAuth();
        $this->model = new UploadedCredential();
    }

    /**
     * List user's uploaded credentials
     */
    public function index()
    {
        $credentials = $this->model->findByUser(currentUserId());

        return $this->view('upload/index', [
            'title' => 'My Uploaded Credentials',
            'credentials' => $credentials,
        ]);
    }

    /**
     * Show upload form
     */
    public function create()
    {
        return $this->view('upload/create', [
            'title' => 'Upload External Credential',
        ]);
    }

    /**
     * Handle upload
     */
    public function store()
    {
        $this->requireCsrf();

        $title = trim($_POST['title'] ?? '');
        $issuer = trim($_POST['issuer'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $externalUrl = trim($_POST['external_url'] ?? '');
        $credentialType = trim($_POST['credential_type'] ?? 'certificate');
        $issuedDate = trim($_POST['issued_date'] ?? '');
        $expirationDate = trim($_POST['expiration_date'] ?? '');

        if (empty($title) || empty($issuer)) {
            flash('error', 'Title and issuer are required.');
            $this->redirect('upload/create');
            return;
        }

        // Handle file upload
        $filePath = '';
        if (!empty($_FILES['credential_file']['tmp_name']) && is_uploaded_file($_FILES['credential_file']['tmp_name'])) {
            $allowed = ['pdf', 'png', 'jpg', 'jpeg', 'gif', 'webp'];
            $ext = strtolower(pathinfo($_FILES['credential_file']['name'], PATHINFO_EXTENSION));
            
            if (!in_array($ext, $allowed)) {
                flash('error', 'Invalid file type. Allowed: ' . implode(', ', $allowed));
                $this->redirect('upload/create');
                return;
            }

            if ($_FILES['credential_file']['size'] > 10 * 1024 * 1024) {
                flash('error', 'File too large. Maximum 10MB.');
                $this->redirect('upload/create');
                return;
            }

            $uploadDir = DATA_PATH . '/uploads/' . currentUserId();
            @mkdir($uploadDir, 0700, true);
            $filename = secureUid('upl_') . '.' . $ext;
            $targetPath = $uploadDir . '/' . $filename;

            if (move_uploaded_file($_FILES['credential_file']['tmp_name'], $targetPath)) {
                $filePath = 'uploads/' . currentUserId() . '/' . $filename;
            }
        }

        $id = $this->model->create(
            currentUserId(), $title, $issuer, $description,
            $externalUrl, $filePath, $credentialType, $issuedDate, $expirationDate
        );

        if ($id) {
            flash('success', 'Credential uploaded successfully! Note: This is marked as uploaded, not issued by CertiMe.');
            $this->redirect('upload');
        } else {
            flash('error', 'Failed to save credential.');
            $this->redirect('upload/create');
        }
    }

    /**
     * View uploaded credential detail
     */
    public function show($id)
    {
        $cred = $this->model->findById((int)$id);
        if (!$cred || (int)$cred['user_id'] !== currentUserId()) {
            return $this->view('errors/404', ['title' => 'Not Found']);
        }

        return $this->view('upload/show', [
            'title' => $cred['title'],
            'credential' => $cred,
        ]);
    }

    /**
     * Delete uploaded credential
     */
    public function delete($id)
    {
        $this->requireCsrf();

        $cred = $this->model->findById((int)$id);
        if (!$cred || (int)$cred['user_id'] !== currentUserId()) {
            flash('error', 'Credential not found.');
            $this->redirect('upload');
            return;
        }

        // Delete file if exists
        if (!empty($cred['file_path'])) {
            $fullPath = DATA_PATH . '/' . $cred['file_path'];
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }

        $this->model->delete((int)$id);
        flash('success', 'Uploaded credential removed.');
        $this->redirect('upload');
    }
}
