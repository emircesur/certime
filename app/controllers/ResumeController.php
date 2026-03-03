<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Credential;
use App\Models\User;
use App\Models\SkillTaxonomy;

/**
 * ResumeController — Export portfolio as verifiable digital resume/CV
 */
class ResumeController extends Controller
{
    public function __construct()
    {
        $this->requireAuth();
    }

    /**
     * Preview resume
     */
    public function index()
    {
        $data = $this->getResumeData(currentUserId());

        return $this->view('portfolio/resume', [
            'title' => 'My Digital Resume',
            'user' => $data['user'],
            'credentials' => $data['credentials'],
            'skills' => $data['skills'],
            'education' => $data['education'],
        ]);
    }

    /**
     * Export resume as JSON-LD (Verifiable Presentation)
     */
    public function exportJson()
    {
        $data = $this->getResumeData(currentUserId());

        $vp = [
            '@context' => [
                'https://www.w3.org/2018/credentials/v1',
                'https://purl.imsglobal.org/spec/ob/v3p0/context-3.0.3.json',
            ],
            'type' => ['VerifiablePresentation'],
            'id' => absUrl('resume/' . $data['user']['id'] . '/json'),
            'holder' => [
                'id' => absUrl('portfolio/' . ($data['user']['portfolio_slug'] ?? $data['user']['id'])),
                'type' => 'Profile',
                'name' => $data['user']['username'],
            ],
            'verifiableCredential' => array_map(function($c) {
                return json_decode($c['badge_jsonld'] ?? '{}', true);
            }, $data['credentials']),
            'generated' => date('c'),
        ];

        // Sign the VP
        if (file_exists(KEYS_PATH . '/issuer.key')) {
            $key = file_get_contents(KEYS_PATH . '/issuer.key');
            $canonical = json_encode($vp, JSON_UNESCAPED_SLASHES);
            $signature = sodium_crypto_sign_detached($canonical, $key);
            $vp['proof'] = [
                'type' => 'Ed25519Signature2020',
                'created' => date('c'),
                'proofPurpose' => 'authentication',
                'verificationMethod' => absUrl('lti/jwks'),
                'proofValue' => base64_encode($signature),
            ];
        }

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="resume_' . date('Y-m-d') . '.json"');
        echo json_encode($vp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit();
    }

    /**
     * Export resume as PDF
     */
    public function exportPdf()
    {
        $data = $this->getResumeData(currentUserId());

        // Use TCPDF to generate a resume PDF
        require_once APP_PATH . '/lib/tecnickcom/tcpdf/tcpdf.php';

        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8');
        $pdf->SetCreator('CertiMe');
        $pdf->SetAuthor($data['user']['username']);
        $pdf->SetTitle('Digital Resume — ' . $data['user']['username']);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(20, 20, 20);
        $pdf->AddPage();

        // Header
        $pdf->SetFont('helvetica', 'B', 24);
        $pdf->Cell(0, 15, $data['user']['username'], 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(0, 8, $data['user']['email'], 0, 1, 'C');
        $pdf->Ln(5);

        // Divider
        $pdf->SetDrawColor(100, 100, 100);
        $pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
        $pdf->Ln(8);

        // Skills section
        if (!empty($data['skills'])) {
            $pdf->SetFont('helvetica', 'B', 14);
            $pdf->Cell(0, 10, 'Skills & Competencies', 0, 1);
            $pdf->SetFont('helvetica', '', 10);
            $skillNames = array_column($data['skills'], 'name');
            $pdf->MultiCell(0, 6, implode(' • ', $skillNames), 0, 'L');
            $pdf->Ln(5);
        }

        // Credentials section
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'Verified Credentials', 0, 1);

        foreach ($data['credentials'] as $cred) {
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->Cell(0, 7, $cred['course_name'], 0, 1);
            $pdf->SetFont('helvetica', '', 9);
            $pdf->SetTextColor(100, 100, 100);
            $issued = $cred['issued_date'] ?? 'N/A';
            $issuer = $cred['issuer_name'] ?? 'CertiMe';
            $pdf->Cell(0, 5, "Issued by {$issuer} on {$issued}", 0, 1);
            $pdf->SetTextColor(0, 0, 0);

            if (!empty($cred['description'])) {
                $pdf->SetFont('helvetica', '', 9);
                $pdf->MultiCell(0, 5, $cred['description'], 0, 'L');
            }

            $verifyUrl = absUrl('credential/' . $cred['credential_uid']);
            $pdf->SetFont('helvetica', 'I', 8);
            $pdf->SetTextColor(0, 100, 200);
            $pdf->Cell(0, 5, 'Verify: ' . $verifyUrl, 0, 1, '', false, $verifyUrl);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Ln(3);
        }

        // Footer info
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->SetTextColor(128, 128, 128);
        $pdf->Cell(0, 5, 'Generated by CertiMe on ' . date('F j, Y') . ' | All credentials are cryptographically verifiable', 0, 1, 'C');

        $pdf->Output('resume_' . date('Y-m-d') . '.pdf', 'D');
        exit();
    }

    private function getResumeData(int $userId): array
    {
        $userModel = new User();
        $user = $userModel->findById($userId);

        $credModel = new Credential();
        $credentials = $credModel->findByUser($userId);
        // Only active credentials
        $credentials = array_filter($credentials, fn($c) => $c['status'] === 'active');

        // Aggregate skills
        $skillModel = new SkillTaxonomy();
        $allSkills = [];
        foreach ($credentials as $cred) {
            $skills = $skillModel->getCredentialSkills($cred['credential_uid']);
            foreach ($skills as $s) {
                $allSkills[$s['code']] = $s;
            }
        }

        // Education from credentials
        $education = array_map(fn($c) => [
            'name' => $c['course_name'],
            'issuer' => $c['issuer_name'],
            'date' => $c['issued_date'],
            'grade' => $c['grade'] ?? null,
        ], $credentials);

        return [
            'user' => $user,
            'credentials' => array_values($credentials),
            'skills' => array_values($allSkills),
            'education' => $education,
        ];
    }
}
