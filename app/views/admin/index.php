<?php
$title = 'Admin Dashboard';
require APP_PATH . '/views/partials/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <!-- Admin Sidebar -->
        <div class="col-lg-2">
            <?php require APP_PATH . '/views/admin/partials/nav.php'; ?>
        </div>

        <!-- Main Content -->
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1">Dashboard</h4>
                    <p class="text-muted mb-0">Overview of your credentialing platform</p>
                </div>
                <a href="<?= url('admin/create') ?>" class="btn btn-primary rounded-pill">
                    <span class="material-symbols-rounded btn-icon">add</span>
                    New Credential
                </a>
            </div>

            <!-- Stats Cards -->
            <div class="row g-3 mb-4">
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-card-icon bg-primary-subtle">
                            <span class="material-symbols-rounded" style="color:var(--md-primary)">school</span>
                        </div>
                        <div>
                            <div class="stat-card-value"><?= $stats['credentials'] ?? 0 ?></div>
                            <div class="stat-card-label">Credentials</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-card-icon bg-secondary-subtle">
                            <span class="material-symbols-rounded" style="color:var(--md-secondary)">group</span>
                        </div>
                        <div>
                            <div class="stat-card-value"><?= $stats['users'] ?? 0 ?></div>
                            <div class="stat-card-label">Users</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-card-icon bg-tertiary-subtle">
                            <span class="material-symbols-rounded" style="color:var(--md-tertiary)">thumb_up</span>
                        </div>
                        <div>
                            <div class="stat-card-value"><?= $stats['endorsements'] ?? 0 ?></div>
                            <div class="stat-card-label">Endorsements</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-card-icon" style="background:rgba(var(--md-error-rgb),.1)">
                            <span class="material-symbols-rounded" style="color:var(--md-error)">key</span>
                        </div>
                        <div>
                            <div class="stat-card-value">
                                <?= $stats['keys_present'] ? '<span class="text-success">Active</span>' : '<span class="text-danger">Missing</span>' ?>
                            </div>
                            <div class="stat-card-label">Signing Keys</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Credentials -->
            <div class="material-card mb-4">
                <div class="card-header-material">
                    <h6 class="fw-semibold mb-0">Recent Credentials</h6>
                    <a href="<?= url('admin/credentials') ?>" class="btn btn-sm btn-outline-secondary rounded-pill">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Recipient</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Issued</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $creds = $stats['recent_credentials'] ?? [];
                            if (empty($creds)):
                            ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No credentials yet</td></tr>
                            <?php else: foreach ($creds as $c): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="material-symbols-rounded text-muted" style="font-size:20px">person</span>
                                        <?= e($c['full_name'] ?? $c['recipient_name'] ?? 'N/A') ?>
                                    </div>
                                </td>
                                <td>
                                    <a href="<?= url('credential/' . e($c['credential_uid'])) ?>" class="text-decoration-none fw-medium">
                                        <?= e($c['course_name']) ?>
                                    </a>
                                </td>
                                <td><span class="badge bg-primary-subtle text-primary rounded-pill"><?= e($c['category'] ?? 'General') ?></span></td>
                                <td>
                                    <?php if (($c['status'] ?? 'active') === 'revoked'): ?>
                                        <span class="badge bg-danger-subtle text-danger rounded-pill">Revoked</span>
                                    <?php else: ?>
                                        <span class="badge bg-success-subtle text-success rounded-pill">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small"><?= date('M j, Y', strtotime($c['issuance_date'])) ?></td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row g-3">
                <div class="col-md-4">
                    <a href="<?= url('admin/create') ?>" class="quick-action-card">
                        <span class="material-symbols-rounded" style="font-size:32px;color:var(--md-primary)">add_circle</span>
                        <div class="fw-semibold">Issue Credential</div>
                        <div class="text-muted small">Create a new digital credential</div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="<?= url('admin/users') ?>" class="quick-action-card">
                        <span class="material-symbols-rounded" style="font-size:32px;color:var(--md-secondary)">manage_accounts</span>
                        <div class="fw-semibold">Manage Users</div>
                        <div class="text-muted small">View and manage user accounts</div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="<?= url('admin/keys') ?>" class="quick-action-card">
                        <span class="material-symbols-rounded" style="font-size:32px;color:var(--md-tertiary)">vpn_key</span>
                        <div class="fw-semibold">Signing Keys</div>
                        <div class="text-muted small">Manage cryptographic keys</div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
