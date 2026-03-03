<?php require APP_PATH . '/views/partials/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-2"><?php require APP_PATH . '/views/admin/partials/nav.php'; ?></div>
        <div class="col-lg-10">
            <h4 class="fw-bold mb-4">Cryptographic Revocation List (CRL) Manager</h4>
            <p class="text-muted">Total permanently revoked: <?= $totalRevoked ?></p>

            <!-- Revoke form -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h6 class="mb-3">Permanently Revoke Credential</h6>
                    <form method="POST" action="<?= url('admin/crl/revoke') ?>">
                        <?= csrfField() ?>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <input type="text" name="credential_uid" class="form-control" placeholder="Credential UID" required>
                            </div>
                            <div class="col-md-5">
                                <input type="text" name="reason" class="form-control" placeholder="Reason for revocation" required>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-danger w-100" onclick="return confirm('This will permanently revoke the credential and recalculate the Merkle tree.')">
                                    <span class="material-symbols-rounded btn-icon">block</span> Revoke
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Credential UID</th><th>Course</th><th>Reason</th><th>Revoked By</th><th>Date</th></tr>
                        </thead>
                        <tbody>
                        <?php if (empty($revocations)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No revocations.</td></tr>
                        <?php else: ?>
                            <?php foreach ($revocations as $r): ?>
                            <tr>
                                <td><code><?= e($r['credential_uid']) ?></code></td>
                                <td><?= e($r['course_name'] ?? 'N/A') ?></td>
                                <td><?= e($r['reason']) ?></td>
                                <td><?= e($r['revoked_by_name'] ?? 'System') ?></td>
                                <td><?= e($r['created_at']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
