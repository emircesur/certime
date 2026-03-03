<?php
$title = 'Key Rotation Manager';
require APP_PATH . '/views/partials/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-2">
            <?php require APP_PATH . '/views/admin/partials/nav.php'; ?>
        </div>
        <div class="col-lg-10">
            <div class="mb-4">
                <h4 class="fw-bold mb-1">
                    <span class="material-symbols-rounded align-middle me-2">sync_lock</span>
                    Key Rotation Manager
                </h4>
                <p class="text-muted mb-0">Securely rotate cryptographic signing keys without invalidating existing credentials</p>
            </div>

            <!-- Warning Banner -->
            <div class="alert alert-info d-flex gap-3 mb-4">
                <span class="material-symbols-rounded mt-1">info</span>
                <div>
                    <strong>How Key Rotation Works</strong>
                    <p class="mb-0 small">When you rotate keys, the current keys are archived. Previously issued credentials remain verifiable because their signatures are checked against both the current key and all archived keys. New credentials will be signed with the new key.</p>
                </div>
            </div>

            <div class="row g-4">
                <!-- Ed25519 Key Rotation -->
                <div class="col-lg-6">
                    <div class="material-card h-100">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="material-symbols-rounded" style="font-size:28px;color:var(--md-primary)">key</span>
                            <h5 class="fw-semibold mb-0">Ed25519 Signing Key</h5>
                        </div>

                        <?php if ($hasEd25519): ?>
                        <div class="alert alert-success d-flex align-items-center gap-2 mb-3">
                            <span class="material-symbols-rounded">check_circle</span>
                            <div>
                                <strong>Active</strong>
                                <div class="small">Since: <?= e($currentKeyDate) ?></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Current Public Key (Hex)</label>
                            <div class="bg-body-tertiary rounded p-2">
                                <code class="small text-break" style="word-break:break-all"><?= e($currentPubKey) ?></code>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-warning mb-3">
                            <span class="material-symbols-rounded align-middle">warning</span>
                            No Ed25519 keys found. Generate keys first from the Keys page.
                        </div>
                        <?php endif; ?>

                        <p class="text-muted small">
                            <strong><?= $credentialCount ?></strong> credentials signed with the current key.
                        </p>

                        <form method="POST" action="<?= url('admin/rotate-ed25519') ?>">
                            <?= csrfField() ?>
                            <button type="submit" class="btn btn-warning rounded-pill"
                                    onclick="return confirm('Rotate Ed25519 keys?\n\nThe current key will be archived and a new keypair will be generated.\nPreviously issued credentials will remain verifiable via the archived key.');"
                                    <?= !$hasEd25519 ? 'disabled' : '' ?>>
                                <span class="material-symbols-rounded btn-icon">autorenew</span>
                                Rotate Ed25519 Key
                            </button>
                        </form>
                    </div>
                </div>

                <!-- PDF Key Rotation -->
                <div class="col-lg-6">
                    <div class="material-card h-100">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="material-symbols-rounded" style="font-size:28px;color:var(--md-tertiary)">picture_as_pdf</span>
                            <h5 class="fw-semibold mb-0">PDF Signing Certificate</h5>
                        </div>

                        <?php if ($hasPdfKeys): ?>
                        <div class="alert alert-success d-flex align-items-center gap-2 mb-3">
                            <span class="material-symbols-rounded">check_circle</span>
                            <strong>Active</strong>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-warning mb-3">
                            <span class="material-symbols-rounded align-middle">warning</span>
                            No PDF signing keys found. Generate from the Keys page first.
                        </div>
                        <?php endif; ?>

                        <p class="text-muted small">
                            The X.509 certificate used to digitally sign PDF certificates. Rotating this creates a new self-signed certificate.
                        </p>

                        <form method="POST" action="<?= url('admin/rotate-pdf-keys') ?>">
                            <?= csrfField() ?>
                            <button type="submit" class="btn btn-warning rounded-pill"
                                    onclick="return confirm('Rotate PDF signing certificate?\n\nThe current certificate will be archived.');"
                                    <?= !$hasPdfKeys ? 'disabled' : '' ?>>
                                <span class="material-symbols-rounded btn-icon">autorenew</span>
                                Rotate PDF Certificate
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Archived Keys -->
            <div class="material-card mt-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="material-symbols-rounded" style="font-size:28px">inventory_2</span>
                    <h5 class="fw-semibold mb-0">Archived Keys</h5>
                </div>
                <p class="text-muted small">Previous keys kept for verification of older credentials.</p>

                <?php if (empty($archives)): ?>
                    <div class="text-center py-4">
                        <span class="material-symbols-rounded mb-2" style="font-size:40px;color:var(--md-outline)">folder_open</span>
                        <p class="text-muted">No archived keys yet. Keys are archived when you rotate them.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Key Name</th>
                                    <th>Archived Date</th>
                                    <th>Public Key (Hex)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($archives as $arch): ?>
                                <tr>
                                    <td class="fw-medium"><?= e($arch['name']) ?></td>
                                    <td class="text-muted"><?= e($arch['date']) ?></td>
                                    <td>
                                        <code class="small" style="word-break:break-all"><?= e(substr($arch['pubKey'], 0, 32)) ?>...</code>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Verify with archived key -->
            <div class="material-card mt-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="material-symbols-rounded" style="font-size:28px;color:var(--md-primary)">verified</span>
                    <h5 class="fw-semibold mb-0">Verify Credential Against Archived Keys</h5>
                </div>
                <p class="text-muted small">Test if a credential signed with an old key can still be verified.</p>
                <div class="input-group" style="max-width:500px">
                    <input type="text" class="form-control" id="verifyUid" placeholder="Enter credential UID (e.g. cert_...)">
                    <button class="btn btn-primary rounded-end-pill" onclick="verifyArchived()">
                        <span class="material-symbols-rounded btn-icon">search</span>
                        Verify
                    </button>
                </div>
                <div id="verifyResult" class="mt-3" style="display:none"></div>
            </div>
        </div>
    </div>
</div>

<script>
function verifyArchived() {
    const uid = document.getElementById('verifyUid').value.trim();
    if (!uid) return;
    const resultDiv = document.getElementById('verifyResult');
    resultDiv.style.display = 'block';
    resultDiv.innerHTML = '<div class="spinner-border spinner-border-sm"></div> Verifying...';

    fetch('<?= url('admin/verify-archived') ?>?uid=' + encodeURIComponent(uid))
        .then(r => r.json())
        .then(data => {
            if (data.verified) {
                resultDiv.innerHTML = '<div class="alert alert-success d-flex align-items-center gap-2"><span class="material-symbols-rounded">check_circle</span> <strong>Verified</strong> — using ' + (data.verifiedWith === 'current' ? 'current key' : 'archived key: ' + data.verifiedWith) + '</div>';
            } else {
                resultDiv.innerHTML = '<div class="alert alert-danger d-flex align-items-center gap-2"><span class="material-symbols-rounded">error</span> <strong>Not Verified</strong> — Could not verify with any key (current or archived).</div>';
            }
        })
        .catch(() => { resultDiv.innerHTML = '<div class="alert alert-danger">Network error</div>'; });
}
</script>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
