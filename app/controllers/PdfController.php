<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Credential;
use App\Models\User;

use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Writer;

class PdfController extends Controller
{
    public function download($credentialUid)
    {
        $credentialModel = new Credential();
        $credential = $credentialModel->findByUid($credentialUid);

        if (!$credential) {
            return $this->view('errors/404', ['title' => 'Not Found']);
        }

        $userModel = new User();
        $user = $userModel->findById($credential['user_id']);
        
        $signedRequested = isset($_GET['signed']) && $_GET['signed'] === '1';
        $encryptRequested = isset($_GET['encrypt']) && $_GET['encrypt'] === '1';
        $template = $_GET['template'] ?? ($credential['pdf_template'] ?? 'classic');
        $validTemplates = ['classic', 'modern', 'minimal', 'professional', 'elegant'];
        if (!in_array($template, $validTemplates)) $template = 'classic';

        $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);

        $pdf->SetCreator(APP_NAME);
        $pdf->SetAuthor(APP_NAME);
        $pdf->SetTitle('Certificate: ' . $credential['course_name']);
        $pdf->SetSubject('Digital Certificate');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Handle signing
        $certPath = KEYS_PATH . '/pdf_signer.crt';
        $keyPath = KEYS_PATH . '/pdf_signer.key';

        if ($signedRequested && (!file_exists($certPath) || !file_exists($keyPath))) {
            if (isAdmin()) {
                flash('error', 'Signing keys not found. Please generate or upload them first.');
                $this->redirect('admin/keys');
            } else {
                http_response_code(409);
                return $this->view('errors/missing_keys', ['title' => 'Signing Keys Missing']);
            }
        }

        if ($signedRequested && file_exists($certPath) && file_exists($keyPath)) {
            try {
                $certificate = 'file://' . realpath($certPath);
                $privateKey = 'file://' . realpath($keyPath);
                $info = [
                    'Name' => APP_NAME,
                    'Location' => 'Online',
                    'Reason' => 'Credential Verification',
                    'ContactInfo' => BASE_URL,
                ];
                $pdf->setSignature($certificate, $privateKey, '', '', 2, $info);
            } catch (\Exception $e) {
                error_log("PDF signing failed: " . $e->getMessage());
            }
        }

        $pdf->AddPage('L', 'A4');

        $pageW = $pdf->getPageWidth();
        $pageH = $pdf->getPageHeight();
        $displayName = $user['full_name'] ?: $user['username'];

        // Render template
        match ($template) {
            'modern' => $this->renderModern($pdf, $credential, $displayName, $pageW, $pageH, $signedRequested),
            'minimal' => $this->renderMinimal($pdf, $credential, $displayName, $pageW, $pageH, $signedRequested),
            'professional' => $this->renderProfessional($pdf, $credential, $displayName, $pageW, $pageH, $signedRequested),
            'elegant' => $this->renderElegant($pdf, $credential, $displayName, $pageW, $pageH, $signedRequested),
            default => $this->renderClassic($pdf, $credential, $displayName, $pageW, $pageH, $signedRequested),
        };

        // QR Code (all templates)
        try {
            $verificationUrl = absUrl('credential/' . $credential['credential_uid']);
            $renderer = new GDLibRenderer(200, 4);
            $writer = new Writer($renderer);
            $qrImage = $writer->writeString($verificationUrl);
            $pdf->Image('@' . $qrImage, $pageW - 48, $pageH - 48, 32, 32, 'PNG');
            
            $pdf->SetFont('helvetica', '', 7);
            $pdf->SetXY($pageW - 52, $pageH - 15);
            $pdf->Cell(40, 5, 'Scan to verify', 0, 0, 'C');
        } catch (\Exception $e) {
            error_log("QR code generation failed: " . $e->getMessage());
        }

        // Encryption
        if ($encryptRequested) {
            try {
                $ownerPass = bin2hex(random_bytes(6));
                $pdf->SetProtection(['copy', 'modify'], '', $ownerPass);
            } catch (\Throwable $e) {
                // Ignore
            }
        }

        $filename = 'certificate-' . $credential['credential_uid'] . '.pdf';
        $pdf->Output($filename, 'I');
        exit;
    }

    // =========================================================================
    // Template: Classic (original)
    // =========================================================================
    private function renderClassic($pdf, $credential, $displayName, $pageW, $pageH, $signed)
    {
        // Certificate border
        $pdf->SetLineStyle(['width' => 0.5, 'color' => [41, 98, 255]]);
        $pdf->Rect(8, 8, $pageW - 16, $pageH - 16);
        $pdf->SetLineStyle(['width' => 0.3, 'color' => [100, 149, 237]]);
        $pdf->Rect(12, 12, $pageW - 24, $pageH - 24);

        $pdf->SetFont('helvetica', '', 14);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->SetXY(0, 20);
        $pdf->Cell(0, 10, APP_NAME . ' Digital Credentialing Platform', 0, 1, 'C');

        $pdf->SetFont('helvetica', 'B', 32);
        $pdf->SetTextColor(33, 33, 33);
        $pdf->Cell(0, 20, 'Certificate of Achievement', 0, 1, 'C');

        $pdf->SetLineStyle(['width' => 0.8, 'color' => [41, 98, 255]]);
        $pdf->Line($pageW/2 - 60, $pdf->GetY(), $pageW/2 + 60, $pdf->GetY());
        $pdf->Ln(8);

        $pdf->SetFont('helvetica', '', 16);
        $pdf->SetTextColor(80, 80, 80);
        $pdf->Cell(0, 12, 'This is to certify that', 0, 1, 'C');

        $pdf->SetFont('helvetica', 'B', 24);
        $pdf->SetTextColor(33, 33, 33);
        $pdf->Cell(0, 16, $displayName, 0, 1, 'C');

        $pdf->SetFont('helvetica', '', 16);
        $pdf->SetTextColor(80, 80, 80);
        $pdf->Cell(0, 12, 'has successfully completed', 0, 1, 'C');

        $pdf->SetFont('helvetica', 'BI', 20);
        $pdf->SetTextColor(41, 98, 255);
        $pdf->Cell(0, 14, $credential['course_name'], 0, 1, 'C');

        if (!empty($credential['description'])) {
            $pdf->SetFont('helvetica', '', 11);
            $pdf->SetTextColor(120, 120, 120);
            $pdf->MultiCell(200, 8, $credential['description'], 0, 'C', false, 1, ($pageW - 200) / 2);
        }

        $this->renderFooter($pdf, $credential, $displayName, $pageW, $pageH, $signed);
    }

    // =========================================================================
    // Template: Modern
    // =========================================================================
    private function renderModern($pdf, $credential, $displayName, $pageW, $pageH, $signed)
    {
        // Gradient-style left accent bar
        $pdf->SetFillColor(41, 98, 255);
        $pdf->Rect(0, 0, 12, $pageH, 'F');

        // Secondary accent
        $pdf->SetFillColor(255, 214, 0);
        $pdf->Rect(12, 0, 3, $pageH, 'F');

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetTextColor(41, 98, 255);
        $pdf->SetXY(25, 18);
        $pdf->Cell(100, 8, strtoupper(APP_NAME), 0, 1, 'L');

        $pdf->SetFont('helvetica', '', 36);
        $pdf->SetTextColor(33, 33, 33);
        $pdf->SetXY(25, 30);
        $pdf->Cell(0, 18, 'CERTIFICATE', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 18);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->SetXY(25, 48);
        $pdf->Cell(0, 10, 'OF ACHIEVEMENT', 0, 1, 'L');

        // Horizontal line
        $pdf->SetLineStyle(['width' => 2, 'color' => [255, 214, 0]]);
        $pdf->Line(25, 62, 120, 62);

        $pdf->SetFont('helvetica', '', 13);
        $pdf->SetTextColor(120, 120, 120);
        $pdf->SetXY(25, 70);
        $pdf->Cell(0, 8, 'This certificate is proudly presented to', 0, 1, 'L');

        $pdf->SetFont('helvetica', 'B', 28);
        $pdf->SetTextColor(33, 33, 33);
        $pdf->SetXY(25, 82);
        $pdf->Cell(0, 16, $displayName, 0, 1, 'L');

        $pdf->SetFont('helvetica', '', 13);
        $pdf->SetTextColor(120, 120, 120);
        $pdf->SetXY(25, 102);
        $pdf->Cell(0, 8, 'for the successful completion of', 0, 1, 'L');

        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->SetTextColor(41, 98, 255);
        $pdf->SetXY(25, 114);
        $pdf->Cell(0, 12, $credential['course_name'], 0, 1, 'L');

        if (!empty($credential['description'])) {
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetTextColor(140, 140, 140);
            $pdf->SetXY(25, 130);
            $pdf->MultiCell(200, 6, $credential['description'], 0, 'L');
        }

        $this->renderFooter($pdf, $credential, $displayName, $pageW, $pageH, $signed);
    }

    // =========================================================================
    // Template: Minimal
    // =========================================================================
    private function renderMinimal($pdf, $credential, $displayName, $pageW, $pageH, $signed)
    {
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(180, 180, 180);
        $pdf->SetXY(0, 25);
        $pdf->Cell(0, 8, strtoupper(APP_NAME . ' · VERIFIED CREDENTIAL'), 0, 1, 'C');

        $pdf->Ln(15);

        $pdf->SetFont('helvetica', 'B', 28);
        $pdf->SetTextColor(33, 33, 33);
        $pdf->Cell(0, 16, $displayName, 0, 1, 'C');

        // Thin line
        $pdf->SetLineStyle(['width' => 0.3, 'color' => [200, 200, 200]]);
        $pdf->Line($pageW/2 - 40, $pdf->GetY() + 2, $pageW/2 + 40, $pdf->GetY() + 2);
        $pdf->Ln(8);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->SetTextColor(120, 120, 120);
        $pdf->Cell(0, 8, 'has earned', 0, 1, 'C');

        $pdf->SetFont('helvetica', 'B', 22);
        $pdf->SetTextColor(33, 33, 33);
        $pdf->Cell(0, 14, $credential['course_name'], 0, 1, 'C');

        if (!empty($credential['description'])) {
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetTextColor(160, 160, 160);
            $pdf->MultiCell(220, 6, $credential['description'], 0, 'C', false, 1, ($pageW - 220) / 2);
        }

        $this->renderFooter($pdf, $credential, $displayName, $pageW, $pageH, $signed);
    }

    // =========================================================================
    // Template: Professional
    // =========================================================================
    private function renderProfessional($pdf, $credential, $displayName, $pageW, $pageH, $signed)
    {
        // Header bar
        $pdf->SetFillColor(25, 25, 50);
        $pdf->Rect(0, 0, $pageW, 45, 'F');

        $pdf->SetFont('helvetica', 'B', 24);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetXY(0, 10);
        $pdf->Cell(0, 12, 'CERTIFICATE OF ACHIEVEMENT', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 11);
        $pdf->SetTextColor(200, 200, 220);
        $pdf->Cell(0, 8, APP_NAME . ' Digital Credentialing Platform', 0, 1, 'C');

        // Gold accent line
        $pdf->SetLineStyle(['width' => 2, 'color' => [212, 175, 55]]);
        $pdf->Line(30, 47, $pageW - 30, 47);

        $pdf->SetFont('helvetica', '', 14);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->SetXY(0, 58);
        $pdf->Cell(0, 10, 'This is to certify that', 0, 1, 'C');

        $pdf->SetFont('helvetica', 'B', 26);
        $pdf->SetTextColor(25, 25, 50);
        $pdf->Cell(0, 18, $displayName, 0, 1, 'C');

        // Gold underline
        $pdf->SetLineStyle(['width' => 1, 'color' => [212, 175, 55]]);
        $nameWidth = $pdf->GetStringWidth($displayName);
        $pdf->Line(($pageW - $nameWidth) / 2, $pdf->GetY(), ($pageW + $nameWidth) / 2, $pdf->GetY());
        $pdf->Ln(6);

        $pdf->SetFont('helvetica', '', 14);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 10, 'has successfully completed the program', 0, 1, 'C');

        $pdf->SetFont('helvetica', 'BI', 20);
        $pdf->SetTextColor(25, 25, 50);
        $pdf->Cell(0, 14, $credential['course_name'], 0, 1, 'C');

        if (!empty($credential['credit_hours']) && (float)$credential['credit_hours'] > 0) {
            $pdf->SetFont('helvetica', '', 11);
            $pdf->SetTextColor(212, 175, 55);
            $pdf->Cell(0, 8, $credential['credit_hours'] . ' Credit Hours', 0, 1, 'C');
        }

        if (!empty($credential['description'])) {
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetTextColor(140, 140, 140);
            $pdf->Ln(2);
            $pdf->MultiCell(200, 6, $credential['description'], 0, 'C', false, 1, ($pageW - 200) / 2);
        }

        // Footer bar
        $pdf->SetFillColor(25, 25, 50);
        $pdf->Rect(0, $pageH - 20, $pageW, 20, 'F');

        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(200, 200, 220);
        $pdf->SetY($pageH - 18);
        $pdf->Cell($pageW/2 - 10, 6, 'Issued: ' . date('F j, Y', strtotime($credential['issuance_date'])), 0, 0, 'C');
        $pdf->Cell($pageW/2 - 10, 6, 'ID: ' . $credential['credential_uid'], 0, 0, 'C');
    }

    // =========================================================================
    // Template: Elegant
    // =========================================================================
    private function renderElegant($pdf, $credential, $displayName, $pageW, $pageH, $signed)
    {
        // Ornate border
        $pdf->SetLineStyle(['width' => 1.5, 'color' => [139, 90, 43]]);
        $pdf->Rect(10, 10, $pageW - 20, $pageH - 20);
        $pdf->SetLineStyle(['width' => 0.5, 'color' => [139, 90, 43]]);
        $pdf->Rect(14, 14, $pageW - 28, $pageH - 28);
        
        // Corner ornaments (small diamonds)
        foreach ([[18,18],[18,$pageH-18],[$pageW-18,18],[$pageW-18,$pageH-18]] as [$cx,$cy]) {
            $pdf->SetFillColor(139, 90, 43);
            $s = 3;
            $pdf->Polygon([$cx, $cy-$s, $cx+$s, $cy, $cx, $cy+$s, $cx-$s, $cy], 'F');
        }

        $pdf->SetFont('times', 'I', 12);
        $pdf->SetTextColor(139, 90, 43);
        $pdf->SetXY(0, 25);
        $pdf->Cell(0, 8, APP_NAME, 0, 1, 'C');

        $pdf->SetFont('times', 'B', 36);
        $pdf->SetTextColor(33, 33, 33);
        $pdf->Cell(0, 20, 'Certificate', 0, 1, 'C');
        $pdf->SetFont('times', 'I', 18);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 10, 'of Achievement', 0, 1, 'C');

        // Ornate divider
        $y = $pdf->GetY() + 2;
        $pdf->SetLineStyle(['width' => 0.5, 'color' => [139, 90, 43]]);
        $pdf->Line($pageW/2 - 50, $y, $pageW/2 - 5, $y);
        $pdf->Line($pageW/2 + 5, $y, $pageW/2 + 50, $y);
        $pdf->SetFillColor(139, 90, 43);
        $s = 2;
        $pdf->Polygon([$pageW/2, $y-$s, $pageW/2+$s, $y, $pageW/2, $y+$s, $pageW/2-$s, $y], 'F');
        $pdf->Ln(8);

        $pdf->SetFont('times', '', 14);
        $pdf->SetTextColor(80, 80, 80);
        $pdf->Cell(0, 10, 'Presented with distinction to', 0, 1, 'C');

        $pdf->SetFont('times', 'BI', 28);
        $pdf->SetTextColor(33, 33, 33);
        $pdf->Cell(0, 16, $displayName, 0, 1, 'C');

        $pdf->SetFont('times', '', 14);
        $pdf->SetTextColor(80, 80, 80);
        $pdf->Cell(0, 10, 'in recognition of completing', 0, 1, 'C');

        $pdf->SetFont('times', 'BI', 20);
        $pdf->SetTextColor(139, 90, 43);
        $pdf->Cell(0, 14, $credential['course_name'], 0, 1, 'C');

        if (!empty($credential['description'])) {
            $pdf->SetFont('times', 'I', 10);
            $pdf->SetTextColor(130, 130, 130);
            $pdf->MultiCell(200, 6, $credential['description'], 0, 'C', false, 1, ($pageW - 200) / 2);
        }

        $this->renderFooter($pdf, $credential, $displayName, $pageW, $pageH, $signed);
    }

    // =========================================================================
    // Shared Footer
    // =========================================================================
    private function renderFooter($pdf, $credential, $displayName, $pageW, $pageH, $signed)
    {
        $pdf->SetFont('helvetica', '', 11);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->SetY($pageH - 45);
        $pdf->Cell($pageW / 2 - 15, 8, 'Issued: ' . date('F j, Y', strtotime($credential['issuance_date'])), 0, 0, 'L');
        $pdf->Cell($pageW / 2 - 15, 8, 'ID: ' . $credential['credential_uid'], 0, 1, 'R');
        $pdf->Cell($pageW / 2 - 15, 8, 'Issuer: ' . ($credential['issuer_name'] ?? APP_NAME), 0, 0, 'L');

        if ($signed) {
            $pdf->Cell($pageW / 2 - 15, 8, 'Digitally Signed', 0, 1, 'R');
        }

        if (!empty($credential['expiration_date'])) {
            $pdf->Ln(2);
            $pdf->SetFont('helvetica', '', 9);
            $pdf->SetTextColor(180, 80, 80);
            $pdf->Cell(0, 6, 'Expires: ' . date('F j, Y', strtotime($credential['expiration_date'])), 0, 1, 'L');
        }
    }
}
