<?php require APP_PATH . '/views/partials/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-2"><?php require APP_PATH . '/views/admin/partials/nav.php'; ?></div>
        <div class="col-lg-10">
            <h4 class="fw-bold mb-4">System Health & Quota Dashboard</h4>

            <!-- Quick Stats -->
            <div class="row g-3 mb-4">
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-card-icon bg-primary-subtle">
                            <span class="material-symbols-rounded" style="color:var(--md-primary)">storage</span>
                        </div>
                        <div>
                            <div class="stat-card-value"><?= number_format($dbSize / 1024 / 1024, 2) ?> MB</div>
                            <div class="stat-card-label">Database Size</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-card-icon bg-success-subtle">
                            <span class="material-symbols-rounded" style="color:var(--bs-success)">login</span>
                        </div>
                        <div>
                            <div class="stat-card-value"><?= $sessionCount ?></div>
                            <div class="stat-card-label">Active Sessions</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-card-icon bg-warning-subtle">
                            <span class="material-symbols-rounded" style="color:var(--bs-warning)">history</span>
                        </div>
                        <div>
                            <div class="stat-card-value"><?= $recentAuditCount ?></div>
                            <div class="stat-card-label">Audit Events (24h)</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-card-icon bg-danger-subtle">
                            <span class="material-symbols-rounded" style="color:var(--bs-danger)">vpn_key</span>
                        </div>
                        <div>
                            <div class="stat-card-value"><?= $apiKeyCount ?></div>
                            <div class="stat-card-label">Active API Keys</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Table Counts -->
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-transparent"><h6 class="mb-0">Table Row Counts</h6></div>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <tbody>
                                <?php foreach ($tableCounts as $table => $count): ?>
                                <tr>
                                    <td><code><?= e($table) ?></code></td>
                                    <td class="text-end fw-semibold"><?= number_format($count) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- PHP & Extensions -->
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-transparent"><h6 class="mb-0">Server Info</h6></div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-6"><strong>PHP:</strong> <?= e($phpVersion) ?></div>
                                <div class="col-6"><strong>SQLite:</strong> <?= e($sqliteVersion) ?></div>
                                <div class="col-6"><strong>JSON Storage:</strong> <?= number_format($jsonStorageSize / 1024, 1) ?> KB</div>
                                <div class="col-6"><strong>Tmp Files:</strong> <?= number_format($tmpSize / 1024, 1) ?> KB</div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-transparent"><h6 class="mb-0">PHP Extensions</h6></div>
                        <div class="card-body">
                            <?php foreach ($extensions as $ext => $loaded): ?>
                            <span class="badge bg-<?= $loaded ? 'success' : 'danger' ?> me-1 mb-1">
                                <?= $loaded ? '✓' : '✗' ?> <?= e($ext) ?>
                            </span>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-transparent"><h6 class="mb-0">Signing Keys</h6></div>
                        <div class="card-body">
                            <?php foreach ($keysStatus as $name => $exists): ?>
                            <div class="d-flex justify-content-between mb-1">
                                <code><?= e($name) ?></code>
                                <span class="badge bg-<?= $exists ? 'success' : 'warning' ?>"><?= $exists ? 'Present' : 'Missing' ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
