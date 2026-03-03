<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Evidence;
use App\Models\Credential;

class EvidenceController extends Controller
{
    protected Evidence $model;

    public function __construct()
    {
        $this->requireAuth();
        $this->model = new Evidence();
    }

    /**
     * List evidence for a credential
     */
    public function index($credentialUid)
    {
        $credModel = new Credential();
        $cred = $credModel->findByUid($credentialUid);

        if (!$cred) {
            return $this->view('errors/404', ['title' => 'Not Found']);
        }

        // Only credential owner or staff can manage evidence
        if ((int)$cred['user_id'] !== currentUserId() && !isStaff()) {
            http_response_code(403);
            return $this->view('errors/403', ['title' => 'Forbidden']);
        }

        $evidence = $this->model->findByCredential((int)$cred['id']);

        return $this->view('evidence/index', [
            'title' => 'Evidence — ' . $cred['course_name'],
            'credential' => $cred,
            'evidence' => $evidence,
        ]);
    }

    /**
     * Add evidence to a credential
     */
    public function store($credentialUid)
    {
        $this->requireCsrf();

        $credModel = new Credential();
        $cred = $credModel->findByUid($credentialUid);

        if (!$cred || ((int)$cred['user_id'] !== currentUserId() && !isStaff())) {
            flash('error', 'Access denied.');
            $this->redirect('portfolio');
            return;
        }

        $type = trim($_POST['type'] ?? 'url');
        $title = trim($_POST['title'] ?? '');
        $url = trim($_POST['url'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($title)) {
            flash('error', 'Evidence title is required.');
            $this->redirect('credential/' . $credentialUid . '/evidence');
            return;
        }

        // Handle file upload for evidence
        $filePath = '';
        if ($type === 'file' && !empty($_FILES['evidence_file']['tmp_name'])) {
            $allowed = ['pdf', 'png', 'jpg', 'jpeg', 'gif', 'zip', 'doc', 'docx', 'txt'];
            $ext = strtolower(pathinfo($_FILES['evidence_file']['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed)) {
                flash('error', 'Invalid file type.');
                $this->redirect('credential/' . $credentialUid . '/evidence');
                return;
            }

            if ($_FILES['evidence_file']['size'] > 20 * 1024 * 1024) {
                flash('error', 'File too large. Maximum 20MB.');
                $this->redirect('credential/' . $credentialUid . '/evidence');
                return;
            }

            $uploadDir = DATA_PATH . '/evidence/' . $cred['id'];
            @mkdir($uploadDir, 0700, true);
            $filename = secureUid('ev_') . '.' . $ext;
            $targetPath = $uploadDir . '/' . $filename;

            if (move_uploaded_file($_FILES['evidence_file']['tmp_name'], $targetPath)) {
                $filePath = 'evidence/' . $cred['id'] . '/' . $filename;
            }
        }

        $id = $this->model->create((int)$cred['id'], $type, $title, $url, $description, $filePath);

        if ($id) {
            flash('success', 'Evidence added successfully!');
        } else {
            flash('error', 'Failed to add evidence.');
        }
        $this->redirect('credential/' . $credentialUid . '/evidence');
    }

    /**
     * Delete evidence
     */
    public function delete($credentialUid, $id)
    {
        $this->requireCsrf();

        $ev = $this->model->findById((int)$id);
        if (!$ev) {
            flash('error', 'Evidence not found.');
            $this->redirect('credential/' . $credentialUid . '/evidence');
            return;
        }

        // Delete file if exists
        if (!empty($ev['file_path'])) {
            $fullPath = DATA_PATH . '/' . $ev['file_path'];
            if (file_exists($fullPath)) @unlink($fullPath);
        }

        $this->model->delete((int)$id);
        flash('success', 'Evidence removed.');
        $this->redirect('credential/' . $credentialUid . '/evidence');
    }
}
