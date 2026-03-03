<?php
$title = e($credential['course_name'] ?? 'Credential');
require APP_PATH . '/views/partials/header.php';

$c = $credential;
$isRevoked = ($c['status'] ?? 'active') === 'revoked';
$skills = !empty($c['skills']) ? array_map('trim', explode(',', $c['skills'])) : [];
$credentialType = ucfirst($c['credential_type'] ?? 'certificate');
$creditHours = (float)($c['credit_hours'] ?? 0);

// LinkedIn share URL (use controller-passed variable if available)
if (!isset($linkedinUrl)) {
    $linkedinUrl = 'https://www.linkedin.com/profile/add?startTask=CERTIFICATION_NAME'
        . '&name=' . urlencode($c['course_name'] ?? '')
        . '&organizationName=' . urlencode($c['issuer_name'] ?? 'CertiMe')
        . '&issueYear=' . date('Y', strtotime($c['issuance_date']))
        . '&issueMonth=' . date('n', strtotime($c['issuance_date']))
        . '&certUrl=' . urlencode(absUrl('credential/' . ($c['credential_uid'] ?? '')))
        . '&certId=' . urlencode($c['credential_uid'] ?? '');
}
?>

<div class="container py-5">
    <div class="row g-4">
        <!-- Main Credential Card -->
        <div class="col-lg-8">
            <div class="credential-card">
                <!-- Status Banner -->
                <?php if ($isRevoked): ?>
                <div class="alert alert-danger d-flex align-items-center gap-2 mb-4">
                    <span class="material-symbols-rounded">cancel</span>
                    <strong>This credential has been revoked</strong>
                </div>
                <?php elseif (!empty($c['badge_jsonld'])): ?>
                <div class="alert alert-success d-flex align-items-center gap-2 mb-4">
                    <span class="material-symbols-rounded">verified</span>
                    <strong>Cryptographically verified credential</strong>
                </div>
                <?php endif; ?>

                <!-- Header -->
                <div class="d-flex align-items-start gap-3 mb-4">
                    <div class="credential-badge-icon">
                        <span class="material-symbols-rounded" style="font-size:36px;color:white">workspace_premium</span>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-1"><?= e($c['course_name'] ?? 'Untitled') ?></h3>
                        <p class="text-muted mb-0">
                            Issued by <strong><?= e($c['issuer_name'] ?? 'CertiMe') ?></strong>
                            on <?= date('F j, Y', strtotime($c['issuance_date'])) ?>
                        </p>
                    </div>
                </div>

                <!-- Recipient -->
                <div class="mb-4 p-3 bg-body-tertiary rounded-3">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="text-muted small">Recipient</div>
                            <div class="fw-semibold"><?= e(($recipient['full_name'] ?? '') ?: ($recipient['username'] ?? 'N/A')) ?></div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-muted small">Credential ID</div>
                            <div class="font-monospace small"><?= e($c['credential_uid'] ?? '') ?></div>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <?php if (!empty($c['description'])): ?>
                <div class="mb-4">
                    <h6 class="fw-semibold">Description</h6>
                    <p class="text-muted"><?= nl2br(e($c['description'])) ?></p>
                </div>
                <?php endif; ?>

                <!-- Skills -->
                <?php if (!empty($skills)): ?>
                <div class="mb-4">
                    <h6 class="fw-semibold">Skills</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($skills as $skill): ?>
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">
                            <?= e(trim($skill)) ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Category -->
                <?php if (!empty($c['category'])): ?>
                <div class="mb-4">
                    <h6 class="fw-semibold">Category</h6>
                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-2"><?= e($c['category']) ?></span>
                </div>
                <?php endif; ?>

                <!-- Credential Type & Credits -->
                <div class="mb-4 p-3 bg-body-tertiary rounded-3">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="text-muted small">Type</div>
                            <div class="fw-semibold">
                                <span class="material-symbols-rounded align-middle me-1" style="font-size:18px">
                                    <?php
                                    $typeIcons = ['certificate'=>'workspace_premium','badge'=>'military_tech','degree'=>'school','diploma'=>'description','license'=>'verified_user','micro-credential'=>'token','course'=>'menu_book','workshop'=>'build'];
                                    echo $typeIcons[$c['credential_type'] ?? 'certificate'] ?? 'workspace_premium';
                                    ?>
                                </span>
                                <?= e($credentialType) ?>
                            </div>
                        </div>
                        <?php if ($creditHours > 0): ?>
                        <div class="col-sm-4">
                            <div class="text-muted small">Credit Hours</div>
                            <div class="fw-semibold"><?= $creditHours ?> hrs</div>
                        </div>
                        <?php endif; ?>
                        <div class="col-sm-4">
                            <div class="text-muted small">Standard</div>
                            <div class="fw-semibold small">Open Badges 3.0 / W3C VC</div>
                        </div>
                    </div>
                </div>

                <!-- Endorsements -->
                <?php if (!empty($endorsements)): ?>
                <div class="mb-4">
                    <h6 class="fw-semibold">
                        <span class="material-symbols-rounded align-middle me-1" style="font-size:20px">thumb_up</span>
                        Endorsements (<?= count($endorsements) ?>)
                    </h6>
                    <?php foreach ($endorsements as $end): ?>
                    <div class="d-flex align-items-start gap-2 mb-2 p-2 bg-body-tertiary rounded">
                        <span class="material-symbols-rounded text-success" style="font-size:20px">verified</span>
                        <div>
                            <div class="fw-medium small"><?= e($end['endorser_name']) ?></div>
                            <div class="text-muted small"><?= e($end['comment']) ?></div>
                            <div class="text-muted" style="font-size:0.7rem"><?= date('M j, Y', strtotime($end['created_at'])) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Endorse Form -->
                <?php if (isLoggedIn() && !$isRevoked): ?>
                <div class="border-top pt-4 mt-4">
                    <h6 class="fw-semibold mb-3">Endorse This Credential</h6>
                    <form method="POST" action="<?= url('credential/' . e($c['credential_uid']) . '/endorse') ?>">
                        <?= csrfField() ?>
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="endorser_name" name="endorser_name" 
                                   placeholder="Your Name" required value="<?= e($_SESSION['user_username'] ?? '') ?>">
                            <label for="endorser_name">Your Name</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="email" class="form-control" id="endorser_email" name="endorser_email" 
                                   placeholder="Your Email" required>
                            <label for="endorser_email">Your Email</label>
                        </div>
                        <div class="form-floating mb-3">
                            <textarea class="form-control" id="comment" name="comment" placeholder="Comment" 
                                      style="height:80px" required></textarea>
                            <label for="comment">Endorsement Comment</label>
                        </div>
                        <button type="submit" class="btn btn-outline-primary rounded-pill">
                            <span class="material-symbols-rounded btn-icon">thumb_up</span>
                            Submit Endorsement
                        </button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Actions Card -->
            <div class="material-card mb-4">
                <h6 class="fw-semibold mb-3">Actions</h6>
                <div class="d-grid gap-2">
                    <?php if (!$isRevoked): ?>
                    <a href="<?= url('credential/' . e($c['credential_uid']) . '/pdf') ?>" class="btn btn-primary rounded-pill">
                        <span class="material-symbols-rounded btn-icon">picture_as_pdf</span>
                        Download PDF Certificate
                    </a>
                    <a href="<?= e($linkedinUrl) ?>" target="_blank" class="btn rounded-pill" 
                       style="background:#0077b5;color:white;border:none">
                        <span class="material-symbols-rounded btn-icon">share</span>
                        Add to LinkedIn
                    </a>
                    <a href="<?= url('credential/' . e($c['credential_uid']) . '/badge') ?>" target="_blank" 
                       class="btn btn-outline-secondary rounded-pill">
                        <span class="material-symbols-rounded btn-icon">data_object</span>
                        View Badge JSON
                    </a>
                    <?php endif; ?>
                    <button class="btn btn-outline-secondary rounded-pill" onclick="copyUrl()">
                        <span class="material-symbols-rounded btn-icon">content_copy</span>
                        Copy Share Link
                    </button>
                </div>
            </div>

            <!-- Verification Card -->
            <div class="material-card mb-4">
                <h6 class="fw-semibold mb-3">
                    <span class="material-symbols-rounded align-middle me-1" style="font-size:20px">shield</span>
                    Verification Details
                </h6>
                <div class="small">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Signature</span>
                        <span>
                            <?php if (!empty($c['badge_jsonld'])): ?>
                                <span class="text-success">
                                    <span class="material-symbols-rounded" style="font-size:16px">check_circle</span>
                                    Present
                                </span>
                            <?php else: ?>
                                <span class="text-warning">
                                    <span class="material-symbols-rounded" style="font-size:16px">warning</span>
                                    Missing
                                </span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Algorithm</span>
                        <span>Ed25519Signature2020</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Format</span>
                        <span>Open Badges 3.0</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Status</span>
                        <span class="<?= $isRevoked ? 'text-danger' : 'text-success' ?>">
                            <?= $isRevoked ? 'Revoked' : 'Active' ?>
                        </span>
                    </div>
                </div>

                <?php if (!empty($c['badge_jsonld'])): ?>
                <details class="mt-3">
                    <summary class="small fw-medium cursor-pointer">Badge JSON-LD</summary>
                    <div class="bg-body-tertiary rounded p-2 mt-2">
                        <code class="small text-break" style="font-size:0.7rem"><?= e($c['badge_jsonld']) ?></code>
                    </div>
                </details>
                <?php endif; ?>
            </div>

            <!-- Verify Link -->
            <div class="material-card bg-body-tertiary mb-4">
                <div class="text-center">
                    <span class="material-symbols-rounded mb-2" style="font-size:36px;color:var(--md-primary)">fact_check</span>
                    <h6 class="fw-semibold">Verify Independently</h6>
                    <p class="text-muted small mb-3">Check this credential's authenticity</p>
                    <a href="<?= url('verify') ?>" class="btn btn-outline-primary btn-sm rounded-pill">
                        Go to Verifier
                    </a>
                </div>
            </div>

            <!-- QR Code -->
            <div class="material-card mb-4">
                <div class="text-center">
                    <h6 class="fw-semibold mb-3">
                        <span class="material-symbols-rounded align-middle me-1" style="font-size:20px">qr_code_2</span>
                        Verification QR Code
                    </h6>
                    <div class="qr-code-container mb-2">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?= urlencode(absUrl('credential/' . $c['credential_uid'])) ?>" 
                             alt="QR Code" class="img-fluid rounded" style="max-width:180px">
                    </div>
                    <p class="text-muted small mb-0">Scan to verify this credential</p>
                </div>
            </div>

            <!-- LinkedIn Badge / Embed -->
            <div class="material-card">
                <h6 class="fw-semibold mb-3">
                    <span class="material-symbols-rounded align-middle me-1" style="font-size:20px">code</span>
                    Embed Badge
                </h6>
                <p class="text-muted small mb-2">Add this badge to your website or email signature:</p>
                <div class="bg-body-tertiary rounded p-2 mb-3">
                    <code class="small text-break" id="embedCode">&lt;a href="<?= e(absUrl('credential/' . $c['credential_uid'])) ?>" target="_blank" title="<?= e($c['course_name']) ?> - Verified by CertiMe"&gt;&lt;img src="<?= e(absUrl('credential/' . $c['credential_uid'] . '/badge-image')) ?>" alt="<?= e($c['course_name']) ?>" style="height:48px" /&gt;&lt;/a&gt;</code>
                </div>
                <button class="btn btn-sm btn-outline-secondary rounded-pill w-100" onclick="copyEmbed()">
                    <span class="material-symbols-rounded btn-icon" style="font-size:16px">content_copy</span>
                    Copy Embed Code
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function copyUrl() {
    navigator.clipboard.writeText(window.location.href).then(() => {
        const btn = event.target.closest('button');
        const orig = btn.innerHTML;
        btn.innerHTML = '<span class="material-symbols-rounded btn-icon">check</span> Copied!';
        setTimeout(() => btn.innerHTML = orig, 2000);
    });
}
function copyEmbed() {
    const code = document.getElementById('embedCode').textContent;
    navigator.clipboard.writeText(code).then(() => {
        const btn = event.target.closest('button');
        const orig = btn.innerHTML;
        btn.innerHTML = '<span class="material-symbols-rounded btn-icon">check</span> Copied!';
        setTimeout(() => btn.innerHTML = orig, 2000);
    });
}
</script>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
