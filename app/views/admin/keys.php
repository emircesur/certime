<?php
$title = 'Signing Keys';
require APP_PATH . '/views/partials/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-2">
            <?php require APP_PATH . '/views/admin/partials/nav.php'; ?>
        </div>
        <div class="col-lg-10">
            <div class="mb-4">
                <h4 class="fw-bold mb-1">Signing Keys</h4>
                <p class="text-muted mb-0">Manage cryptographic keys for credential signing</p>
            </div>

            <div class="row g-4">
                <!-- Ed25519 Keys -->
                <div class="col-lg-6">
                    <div class="material-card h-100">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="material-symbols-rounded" style="font-size:28px;color:var(--md-primary)">key</span>
                            <h5 class="fw-semibold mb-0">Ed25519 Keys</h5>
                        </div>
                        <p class="text-muted small">Used for Open Badge 3.0 credential signatures (Ed25519Signature2020).</p>

                        <?php
                        $edKeyPath = DATA_PATH . '/keys/issuer.key';
                        $edPubPath = DATA_PATH . '/keys/issuer.pub';
                        $edKeyExists = file_exists($edKeyPath) && file_exists($edPubPath);
                        ?>

                        <?php if ($edKeyExists): ?>
                            <div class="alert alert-success d-flex align-items-center gap-2 mb-3">
                                <span class="material-symbols-rounded">check_circle</span>
                                <div>
                                    <strong>Keys Active</strong><br>
                                    <span class="small">Created: <?= date('M j, Y H:i', filemtime($edKeyPath)) ?></span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-medium">Public Key (Base64)</label>
                                <div class="bg-body-tertiary rounded p-2">
                                    <code class="small text-break"><?= e(base64_encode(file_get_contents($edPubPath))) ?></code>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning d-flex align-items-center gap-2 mb-3">
                                <span class="material-symbols-rounded">warning</span>
                                <div>
                                    <strong>No Ed25519 Keys Found</strong><br>
                                    <span class="small">Keys will be auto-generated on next page load, or generate below.</span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="<?= url('admin/keys/generate-ed25519') ?>" id="ed25519Form">
                            <?= csrfField() ?>
                            <button type="submit" class="btn btn-outline-primary rounded-pill"
                                    onclick="return confirm('<?= $edKeyExists ? 'Replace existing Ed25519 keys? Existing signatures will still verify with old public key.' : 'Generate new Ed25519 keypair?' ?>')">
                                <span class="material-symbols-rounded btn-icon">autorenew</span>
                                <?= $edKeyExists ? 'Regenerate' : 'Generate' ?> Ed25519 Keys
                            </button>
                        </form>
                    </div>
                </div>

                <!-- PDF Signing Keys -->
                <div class="col-lg-6">
                    <div class="material-card h-100">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="material-symbols-rounded" style="font-size:28px;color:var(--md-tertiary)">picture_as_pdf</span>
                            <h5 class="fw-semibold mb-0">PDF Signing Certificate</h5>
                        </div>
                        <p class="text-muted small">X.509 certificate and private key for TCPDF digital signatures on PDF certificates.</p>

                        <?php
                        $certPath = KEYS_PATH . '/pdf_signer.crt';
                        $pKeyPath = KEYS_PATH . '/pdf_signer.key';
                        $pdfKeysExist = file_exists($certPath) && file_exists($pKeyPath);
                        ?>

                        <?php if ($pdfKeysExist): ?>
                            <div class="alert alert-success d-flex align-items-center gap-2 mb-3">
                                <span class="material-symbols-rounded">check_circle</span>
                                <div>
                                    <strong>Certificate Active</strong><br>
                                    <span class="small">Created: <?= date('M j, Y H:i', filemtime($certPath)) ?></span>
                                </div>
                            </div>
                            <?php
                            $certContent = file_get_contents($certPath);
                            $certData = openssl_x509_parse($certContent);
                            if ($certData): ?>
                            <div class="small mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Subject:</span>
                                    <span><?= e($certData['subject']['CN'] ?? 'N/A') ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Valid Until:</span>
                                    <span><?= date('M j, Y', $certData['validTo_time_t'] ?? 0) ?></span>
                                </div>
                            </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="alert alert-warning d-flex align-items-center gap-2 mb-3">
                                <span class="material-symbols-rounded">warning</span>
                                <div>
                                    <strong>No PDF Signing Certificate</strong><br>
                                    <span class="small">Upload or generate a certificate to enable PDF signing.</span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Generate -->
                        <form method="POST" action="<?= url('admin/keys/generate') ?>" class="mb-3">
                            <?= csrfField() ?>
                            <button type="submit" class="btn btn-outline-primary rounded-pill"
                                    onclick="return confirm('Generate a self-signed X.509 certificate?')">
                                <span class="material-symbols-rounded btn-icon">add_circle</span>
                                Generate Self-Signed Certificate
                            </button>
                        </form>

                        <!-- Upload -->
                        <details class="mt-3">
                            <summary class="fw-medium small cursor-pointer">Upload existing certificate</summary>
                            <form method="POST" action="<?= url('admin/keys/upload') ?>" enctype="multipart/form-data" class="mt-2">
                                <?= csrfField() ?>
                                <div class="mb-2">
                                    <label class="form-label small">Certificate (.crt/.pem)</label>
                                    <input type="file" class="form-control form-control-sm" name="certificate" accept=".crt,.pem" required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Private Key (.key/.pem)</label>
                                    <input type="file" class="form-control form-control-sm" name="private_key" accept=".key,.pem" required>
                                </div>
                                <button type="submit" class="btn btn-sm btn-primary rounded-pill">
                                    <span class="material-symbols-rounded btn-icon" style="font-size:16px">upload</span>
                                    Upload
                                </button>
                            </form>
                        </details>
                    </div>
                </div>
            </div>

            <!-- Key Security Info -->
            <div class="material-card bg-body-tertiary mt-4">
                <div class="d-flex align-items-start gap-3">
                    <span class="material-symbols-rounded" style="font-size:32px;color:var(--md-warning)">shield</span>
                    <div>
                        <h6 class="fw-semibold mb-1">Key Security</h6>
                        <p class="text-muted small mb-0">
                            Private keys are stored in the <code>data/keys/</code> directory which is protected by 
                            <code>.htaccess</code> rules. Never expose private keys publicly. Ed25519 keys are 
                            auto-generated on first run if not present. Back up your keys securely — losing them 
                            means you cannot prove authorship of previously issued credentials.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
