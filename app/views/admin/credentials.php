<?php
$title = 'Manage Credentials';
require APP_PATH . '/views/partials/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-2">
            <?php require APP_PATH . '/views/admin/partials/nav.php'; ?>
        </div>
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1">Credentials</h4>
                    <p class="text-muted mb-0"><?= $total ?? 0 ?> total credentials</p>
                </div>
                <a href="<?= url('admin/create') ?>" class="btn btn-primary rounded-pill">
                    <span class="material-symbols-rounded btn-icon">add</span>
                    Issue New
                </a>
            </div>

            <!-- Search -->
            <div class="material-card mb-4">
                <form method="GET" action="<?= url('admin/credentials') ?>" class="d-flex gap-2">
                    <div class="flex-grow-1">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0">
                                <span class="material-symbols-rounded text-muted">search</span>
                            </span>
                            <input type="text" class="form-control border-start-0" name="q" 
                                   placeholder="Search by title, recipient..." value="<?= e($search ?? '') ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Search</button>
                    <?php if (!empty($search)): ?>
                        <a href="<?= url('admin/credentials') ?>" class="btn btn-outline-secondary rounded-pill">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Credentials Table -->
            <div class="material-card">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>UID</th>
                                <th>Recipient</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Issued</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($credentials)): ?>
                            <tr><td colspan="7" class="text-center text-muted py-5">
                                <span class="material-symbols-rounded d-block mb-2" style="font-size:48px">inbox</span>
                                No credentials found
                            </td></tr>
                            <?php else: foreach ($credentials as $c): ?>
                            <tr>
                                <td><code class="small"><?= e(substr($c['credential_uid'], 0, 8)) ?>...</code></td>
                                <td>
                                    <div class="fw-medium"><?= e($c['full_name'] ?? $c['recipient_name'] ?? 'N/A') ?></div>
                                    <div class="text-muted small"><?= e($c['recipient_email'] ?? '') ?></div>
                                </td>
                                <td>
                                    <a href="<?= url('credential/' . e($c['credential_uid'])) ?>" class="text-decoration-none fw-medium">
                                        <?= e($c['course_name']) ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary rounded-pill">
                                        <?= e($c['category'] ?? 'General') ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (($c['status'] ?? 'active') === 'revoked'): ?>
                                        <span class="badge bg-danger-subtle text-danger rounded-pill">
                                            <span class="material-symbols-rounded" style="font-size:14px">block</span>
                                            Revoked
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-success-subtle text-success rounded-pill">
                                            <span class="material-symbols-rounded" style="font-size:14px">check_circle</span>
                                            Active
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small"><?= date('M j, Y', strtotime($c['issuance_date'])) ?></td>
                                <td class="text-end">
                                    <div class="d-flex gap-1 justify-content-end">
                                        <a href="<?= url('credential/' . e($c['credential_uid'])) ?>" 
                                           class="btn btn-sm btn-outline-primary rounded-pill" title="View">
                                            <span class="material-symbols-rounded" style="font-size:18px">visibility</span>
                                        </a>
                                        <a href="<?= url('credential/' . e($c['credential_uid']) . '/pdf') ?>" 
                                           class="btn btn-sm btn-outline-secondary rounded-pill" title="PDF">
                                            <span class="material-symbols-rounded" style="font-size:18px">picture_as_pdf</span>
                                        </a>
                                        <?php if (($c['status'] ?? 'active') !== 'revoked'): ?>
                                        <form method="POST" action="<?= url('admin/credentials/' . e($c['credential_uid']) . '/revoke') ?>" 
                                              class="d-inline" onsubmit="return confirm('Revoke this credential? This cannot be undone.')">
                                            <?= csrfField() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill" title="Revoke">
                                                <span class="material-symbols-rounded" style="font-size:18px">block</span>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if (($totalPages ?? 1) > 1): ?>
                <div class="d-flex justify-content-center py-3">
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= ($page ?? 1) == $i ? 'active' : '' ?>">
                                <a class="page-link" href="<?= url('admin/credentials?page=' . $i . (!empty($search) ? '&q=' . urlencode($search) : '')) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
