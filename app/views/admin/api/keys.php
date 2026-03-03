<?php require APP_PATH . '/views/partials/header.php'; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">API Keys</h4>
            <p class="text-muted mb-0">Manage API keys for Zapier, Make.com, and external integrations</p>
        </div>
    </div>

    <!-- Create new key -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h6 class="mb-3">Generate New API Key</h6>
            <form method="POST" action="<?= url('api/keys/create') ?>">
                <?= csrfField() ?>
                <div class="row g-2">
                    <div class="col-md-3">
                        <input type="text" name="name" class="form-control" placeholder="Key name (e.g. Zapier)" required>
                    </div>
                    <div class="col-md-4">
                        <select name="scopes[]" class="form-select" multiple size="3">
                            <option value="credentials:read" selected>credentials:read</option>
                            <option value="credentials:write">credentials:write</option>
                            <option value="users:read">users:read</option>
                        </select>
                        <div class="form-text">Hold Ctrl to select multiple scopes.</div>
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="expires_days" class="form-control" value="365" min="1" max="3650">
                        <div class="form-text">Expires in days</div>
                    </div>
                    <div class="col-md-3 d-flex align-items-start">
                        <button type="submit" class="btn btn-primary w-100">Generate Key</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Existing keys -->
    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Name</th><th>Key Prefix</th><th>Scopes</th><th>Last Used</th><th>Expires</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php if (empty($keys)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No API keys yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($keys as $k): ?>
                    <tr>
                        <td><strong><?= e($k['name']) ?></strong></td>
                        <td><code><?= e($k['key_prefix']) ?>...</code></td>
                        <td>
                            <?php foreach (json_decode($k['scopes'] ?? '[]', true) as $s): ?>
                            <span class="badge bg-light text-dark"><?= e($s) ?></span>
                            <?php endforeach; ?>
                        </td>
                        <td class="small"><?= $k['last_used_at'] ? e($k['last_used_at']) : '<span class="text-muted">Never</span>' ?></td>
                        <td class="small"><?= e($k['expires_at'] ?? 'Never') ?></td>
                        <td>
                            <?php if ($k['is_active']): ?>
                            <form method="POST" action="<?= url('api/keys/' . $k['id'] . '/revoke') ?>" class="d-inline">
                                <?= csrfField() ?>
                                <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Revoke this API key?')">Revoke</button>
                            </form>
                            <?php else: ?>
                            <span class="badge bg-danger">Revoked</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- API Docs -->
    <div class="card shadow-sm border-0 mt-4">
        <div class="card-header bg-transparent"><h6 class="mb-0">Quick API Reference</h6></div>
        <div class="card-body">
            <p class="text-muted">Use <code>Authorization: Bearer &lt;your-api-key&gt;</code> header for all requests.</p>
            <table class="table table-sm">
                <thead><tr><th>Method</th><th>Endpoint</th><th>Scope</th><th>Description</th></tr></thead>
                <tbody>
                    <tr><td><span class="badge bg-success">GET</span></td><td><code>/api/v1/credentials</code></td><td>credentials:read</td><td>List your credentials</td></tr>
                    <tr><td><span class="badge bg-success">GET</span></td><td><code>/api/v1/credentials/:uid</code></td><td>credentials:read</td><td>Get credential details</td></tr>
                    <tr><td><span class="badge bg-primary">POST</span></td><td><code>/api/v1/credentials</code></td><td>credentials:write</td><td>Issue new credential</td></tr>
                    <tr><td><span class="badge bg-primary">POST</span></td><td><code>/api/v1/credentials/:uid/revoke</code></td><td>credentials:write</td><td>Revoke credential</td></tr>
                    <tr><td><span class="badge bg-success">GET</span></td><td><code>/api/v1/verify/:uid</code></td><td>credentials:read</td><td>Verify credential</td></tr>
                    <tr><td><span class="badge bg-success">GET</span></td><td><code>/api/v1/user</code></td><td>users:read</td><td>Get API key user info</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
