<?php require APP_PATH . '/views/partials/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-2"><?php require APP_PATH . '/views/admin/partials/nav.php'; ?></div>
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">LTI 1.3 Connections</h4>
            </div>

            <!-- Config Info -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-transparent"><h6 class="mb-0">CertiMe LTI Tool Configuration</h6></div>
                <div class="card-body">
                    <p class="text-muted mb-2">Provide these URLs to your LMS administrator:</p>
                    <div class="row g-2">
                        <div class="col-md-6"><label class="form-label small">Login URL</label><input type="text" class="form-control form-control-sm" readonly value="<?= e($loginUrl) ?>"></div>
                        <div class="col-md-6"><label class="form-label small">Launch URL</label><input type="text" class="form-control form-control-sm" readonly value="<?= e($launchUrl) ?>"></div>
                        <div class="col-md-6"><label class="form-label small">JWKS URL</label><input type="text" class="form-control form-control-sm" readonly value="<?= e($jwksUrl) ?>"></div>
                        <div class="col-md-6"><label class="form-label small">Deep Link URL</label><input type="text" class="form-control form-control-sm" readonly value="<?= e($deepLinkUrl) ?>"></div>
                    </div>
                </div>
            </div>

            <!-- Register Connection -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-transparent"><h6 class="mb-0">Register New LTI Connection</h6></div>
                <div class="card-body">
                    <form method="POST" action="<?= url('admin/lti/register') ?>">
                        <?= csrfField() ?>
                        <div class="row g-2">
                            <div class="col-md-3"><input type="text" name="name" class="form-control" placeholder="Connection name" required></div>
                            <div class="col-md-3"><input type="text" name="client_id" class="form-control" placeholder="Client ID" required></div>
                            <div class="col-md-3"><input type="url" name="issuer" class="form-control" placeholder="Issuer URL"></div>
                            <div class="col-md-3"><input type="url" name="auth_url" class="form-control" placeholder="Auth Login URL"></div>
                            <div class="col-md-3"><input type="url" name="token_url" class="form-control" placeholder="Token URL"></div>
                            <div class="col-md-3"><input type="url" name="jwks_url" class="form-control" placeholder="Platform JWKS URL"></div>
                            <div class="col-md-3"><input type="text" name="deployment_id" class="form-control" placeholder="Deployment ID"></div>
                            <div class="col-md-3"><button type="submit" class="btn btn-primary w-100">Register</button></div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Existing Connections -->
            <div class="card shadow-sm border-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light"><tr><th>Name</th><th>Client ID</th><th>Issuer</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                        <?php if (empty($connections)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No LTI connections.</td></tr>
                        <?php else: ?>
                            <?php foreach ($connections as $c): ?>
                            <tr>
                                <td><strong><?= e($c['name']) ?></strong></td>
                                <td><code><?= e($c['client_id']) ?></code></td>
                                <td class="small"><?= e($c['issuer'] ?? '') ?></td>
                                <td><span class="badge bg-<?= $c['is_active'] ? 'success' : 'secondary' ?>"><?= $c['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                                <td>
                                    <form method="POST" action="<?= url('admin/lti/' . $c['id'] . '/delete') ?>" class="d-inline">
                                        <?= csrfField() ?>
                                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this connection?')">Delete</button>
                                    </form>
                                </td>
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
