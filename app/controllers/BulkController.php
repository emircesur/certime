<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\BulkJob;
use App\Models\Credential;
use App\Models\User;
use App\Lib\OpenBadge;

class BulkController extends Controller
{
    protected BulkJob $jobModel;

    public function __construct()
    {
        $this->requireStaff();
        $this->jobModel = new BulkJob();
    }

    /**
     * Bulk issuance page
     */
    public function index()
    {
        $jobs = $this->jobModel->getAll();

        return $this->view('bulk/index', [
            'title' => 'Bulk Issuance Engine',
            'jobs' => $jobs,
        ]);
    }

    /**
     * Upload CSV and show mapping screen
     */
    public function upload()
    {
        $this->requireCsrf();

        if (empty($_FILES['csv_file']['tmp_name']) || !is_uploaded_file($_FILES['csv_file']['tmp_name'])) {
            flash('error', 'Please upload a CSV file.');
            $this->redirect('admin/bulk');
            return;
        }

        $ext = strtolower(pathinfo($_FILES['csv_file']['name'], PATHINFO_EXTENSION));
        if ($ext !== 'csv') {
            flash('error', 'Only CSV files are accepted.');
            $this->redirect('admin/bulk');
            return;
        }

        // Parse CSV
        $handle = fopen($_FILES['csv_file']['tmp_name'], 'r');
        if (!$handle) {
            flash('error', 'Cannot read CSV file.');
            $this->redirect('admin/bulk');
            return;
        }

        $headers = fgetcsv($handle);
        if (!$headers || count($headers) < 2) {
            fclose($handle);
            flash('error', 'CSV must have at least 2 columns with a header row.');
            $this->redirect('admin/bulk');
            return;
        }

        $rows = [];
        $rowCount = 0;
        while (($row = fgetcsv($handle)) !== false && $rowCount < 5) {
            $rows[] = $row;
            $rowCount++;
        }

        // Count total rows
        $totalRows = $rowCount;
        while (fgetcsv($handle) !== false) {
            $totalRows++;
        }
        fclose($handle);

        // Save CSV temporarily
        $tmpDir = DATA_PATH . '/tmp';
        @mkdir($tmpDir, 0700, true);
        $tmpFile = $tmpDir . '/bulk_' . secureUid('') . '.csv';
        move_uploaded_file($_FILES['csv_file']['tmp_name'], $tmpFile);

        // Store in session for mapping step
        $_SESSION['bulk_csv'] = [
            'file' => $tmpFile,
            'headers' => $headers,
            'preview' => $rows,
            'total_rows' => $totalRows,
            'original_name' => $_FILES['csv_file']['name'],
        ];

        return $this->view('bulk/map', [
            'title' => 'Map CSV Columns',
            'headers' => $headers,
            'preview' => $rows,
            'totalRows' => $totalRows,
        ]);
    }

    /**
     * Process the mapped CSV
     */
    public function process()
    {
        $this->requireCsrf();

        $csvData = $_SESSION['bulk_csv'] ?? null;
        if (!$csvData || !file_exists($csvData['file'])) {
            flash('error', 'No CSV file found. Please upload again.');
            $this->redirect('admin/bulk');
            return;
        }

        // Get column mapping
        $mapping = [
            'email' => (int)($_POST['col_email'] ?? -1),
            'name' => (int)($_POST['col_name'] ?? -1),
            'title' => (int)($_POST['col_title'] ?? -1),
            'description' => (int)($_POST['col_description'] ?? -1),
            'category' => (int)($_POST['col_category'] ?? -1),
            'skills' => (int)($_POST['col_skills'] ?? -1),
            'type' => (int)($_POST['col_type'] ?? -1),
            'credits' => (int)($_POST['col_credits'] ?? -1),
        ];

        if ($mapping['email'] < 0 || $mapping['title'] < 0) {
            flash('error', 'Email and Title columns are required.');
            $this->redirect('admin/bulk');
            return;
        }

        // Create bulk job
        $jobId = $this->jobModel->create(
            currentUserId(),
            $csvData['original_name'],
            $csvData['total_rows'],
            json_encode($mapping)
        );

        if (!$jobId) {
            flash('error', 'Failed to create bulk job.');
            $this->redirect('admin/bulk');
            return;
        }

        // Process CSV
        $handle = fopen($csvData['file'], 'r');
        fgetcsv($handle); // skip headers

        $userModel = new User();
        $credModel = new Credential();
        $successCount = 0;
        $errorCount = 0;
        $errors = [];
        $rowNum = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            try {
                $email = trim($row[$mapping['email']] ?? '');
                $title = trim($row[$mapping['title']] ?? '');

                if (empty($email) || empty($title)) {
                    $errors[] = "Row {$rowNum}: Missing email or title.";
                    $errorCount++;
                    continue;
                }

                // Find or create user
                $user = $userModel->findByEmail($email);
                if (!$user) {
                    // Auto-create user with random password
                    $username = explode('@', $email)[0] . '_' . rand(100, 999);
                    $tmpPass = bin2hex(random_bytes(8));
                    $userId = $userModel->create($username, $email, $tmpPass, 'student');
                    if (!$userId) {
                        $errors[] = "Row {$rowNum}: Failed to create user for {$email}.";
                        $errorCount++;
                        continue;
                    }
                    $user = $userModel->findById($userId);
                }

                $description = $mapping['description'] >= 0 ? trim($row[$mapping['description']] ?? '') : '';
                $category = $mapping['category'] >= 0 ? trim($row[$mapping['category']] ?? 'general') : 'general';
                $skills = $mapping['skills'] >= 0 ? trim($row[$mapping['skills']] ?? '') : '';
                $credType = $mapping['type'] >= 0 ? trim($row[$mapping['type']] ?? 'certificate') : 'certificate';
                $credits = $mapping['credits'] >= 0 ? (float)($row[$mapping['credits']] ?? 0) : 0;

                $credUid = secureUid('cert_');

                $badgeJsonLd = OpenBadge::generate(
                    $credUid, $email, $title, $description,
                    ['category' => $category, 'skills' => $skills, 'credential_type' => $credType, 'credit_hours' => $credits]
                );

                $id = $credModel->create(
                    (int)$user['id'], $credUid, $title, $description,
                    $badgeJsonLd, $category, $skills, APP_NAME, $credType, $credits
                );

                if ($id) {
                    $successCount++;
                } else {
                    $errors[] = "Row {$rowNum}: Failed to create credential for {$email}.";
                    $errorCount++;
                }
            } catch (\Exception $e) {
                $errors[] = "Row {$rowNum}: " . $e->getMessage();
                $errorCount++;
            }

            // Update progress periodically
            if ($rowNum % 10 === 0) {
                $this->jobModel->updateProgress($jobId, $rowNum - 1, $successCount, $errorCount);
            }
        }

        fclose($handle);

        // Final update
        $this->jobModel->updateProgress($jobId, $csvData['total_rows'], $successCount, $errorCount);
        if (!empty($errors)) {
            $this->jobModel->setError($jobId, implode("\n", array_slice($errors, 0, 100)));
        }

        // Clean up
        @unlink($csvData['file']);
        unset($_SESSION['bulk_csv']);

        flash('success', "Bulk issuance complete: {$successCount} issued, {$errorCount} errors.");
        $this->redirect('admin/bulk/job/' . $jobId);
    }

    /**
     * View bulk job details
     */
    public function show($id)
    {
        $job = $this->jobModel->findById((int)$id);
        if (!$job) {
            return $this->view('errors/404', ['title' => 'Not Found']);
        }

        return $this->view('bulk/show', [
            'title' => 'Bulk Job #' . $id,
            'job' => $job,
        ]);
    }
}
