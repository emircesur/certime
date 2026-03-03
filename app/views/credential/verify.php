<?php
$title = 'Verify Credential';
require APP_PATH . '/views/partials/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-5">
                <span class="material-symbols-rounded" style="font-size:64px;color:var(--md-primary)">fact_check</span>
                <h2 class="fw-bold mt-2">Verify a Credential</h2>
                <p class="text-muted">Enter a credential UID to verify its cryptographic authenticity</p>
            </div>

            <!-- Verify Form -->
            <div class="material-card mb-4">
                <form method="POST" action="<?= url('verify') ?>">
                    <?= csrfField() ?>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-transparent">
                            <span class="material-symbols-rounded text-muted">search</span>
                        </span>
                        <input type="text" class="form-control" name="uid" 
                               placeholder="Enter credential UID..." required
                               value="<?= e($uid ?? '') ?>">
                        <button type="submit" class="btn btn-primary px-4">
                            Verify
                        </button>
                    </div>
                    <div class="form-text mt-2">
                        Example: <code>a1b2c3d4e5f6...</code> — The UID can be found on the credential page or PDF certificate.
                    </div>
                </form>
            </div>

            <!-- Verification Result -->
            <?php if (isset($result)): ?>
            <div class="material-card">
                <?php if (isset($result['credential'])): ?>
                    <?php $v = $result['credential']; $valid = $result['valid'] ?? false; ?>

                    <?php if (($v['status'] ?? 'active') === 'revoked'): ?>
                        <div class="text-center py-4">
                            <span class="material-symbols-rounded" style="font-size:72px;color:var(--md-error)">cancel</span>
                            <h4 class="fw-bold mt-3 text-danger">Credential Revoked</h4>
                            <p class="text-muted">This credential has been revoked by the issuer.</p>
                        </div>
                    <?php elseif ($valid): ?>
                        <div class="text-center py-4">
                            <span class="material-symbols-rounded" style="font-size:72px;color:var(--md-success)">verified</span>
                            <h4 class="fw-bold mt-3 text-success">Credential Verified</h4>
                            <p class="text-muted">The cryptographic signature is valid and the credential is authentic.</p>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <span class="material-symbols-rounded" style="font-size:72px;color:var(--md-warning)">gpp_maybe</span>
                            <h4 class="fw-bold mt-3 text-warning">Signature Not Verified</h4>
                            <p class="text-muted">The credential exists but the signature could not be verified.</p>
                        </div>
                    <?php endif; ?>

                    <hr>

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="text-muted small">Title</div>
                            <div class="fw-semibold"><?= e($v['course_name']) ?></div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-muted small">Recipient</div>
                            <div class="fw-semibold"><?= e($v['recipient_name'] ?? $v['full_name'] ?? 'N/A') ?></div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-muted small">Issuer</div>
                            <div><?= e($v['issuer_name'] ?? 'CertiMe') ?></div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-muted small">Issued On</div>
                            <div><?= date('F j, Y', strtotime($v['issuance_date'])) ?></div>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <a href="<?= url('credential/' . e($v['credential_uid'])) ?>" class="btn btn-primary rounded-pill">
                            View Full Credential
                        </a>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <span class="material-symbols-rounded" style="font-size:72px;color:var(--md-error)">search_off</span>
                        <h4 class="fw-bold mt-3">Credential Not Found</h4>
                        <p class="text-muted">No credential matches the UID provided. Please check and try again.</p>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- How Verification Works -->
            <div class="material-card bg-body-tertiary mt-4">
                <h6 class="fw-semibold mb-3">How Verification Works</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="d-flex align-items-start gap-2">
                            <span class="material-symbols-rounded text-primary" style="font-size:20px">fingerprint</span>
                            <div class="small">
                                <div class="fw-medium">Signature Check</div>
                                <div class="text-muted">Ed25519 digital signature verified against public key</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-start gap-2">
                            <span class="material-symbols-rounded text-primary" style="font-size:20px">account_tree</span>
                            <div class="small">
                                <div class="fw-medium">Merkle Proof</div>
                                <div class="text-muted">Inclusion verified in cryptographic Merkle tree</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-start gap-2">
                            <span class="material-symbols-rounded text-primary" style="font-size:20px">badge</span>
                            <div class="small">
                                <div class="fw-medium">Standards</div>
                                <div class="text-muted">Open Badges 3.0 / W3C VC compliant</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
