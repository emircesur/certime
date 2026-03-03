<?php require APP_PATH . '/views/partials/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-2"><?php require APP_PATH . '/views/admin/partials/nav.php'; ?></div>
        <div class="col-lg-10">
            <h4 class="fw-bold mb-4">Pending OTP Claims</h4>

            <div class="card shadow-sm border-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Code</th><th>Credential UID</th><th>Email</th><th>Status</th><th>Expires</th></tr>
                        </thead>
                        <tbody>
                        <?php if (empty($claims)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No pending claims.</td></tr>
                        <?php else: ?>
                            <?php foreach ($claims as $c): ?>
                            <tr>
                                <td><code class="fw-bold"><?= e($c['code']) ?></code></td>
                                <td><a href="<?= url('credential/' . $c['credential_uid']) ?>"><?= e($c['credential_uid']) ?></a></td>
                                <td><?= e($c['email']) ?></td>
                                <td><span class="badge bg-<?= $c['status'] === 'pending' ? 'warning' : 'success' ?>"><?= e($c['status']) ?></span></td>
                                <td><?= e($c['expires_at']) ?></td>
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
