<?php require APP_PATH . '/views/partials/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-2"><?php require APP_PATH . '/views/admin/partials/nav.php'; ?></div>
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1">Dispute & Abuse Resolution Queue</h4>
                    <p class="text-muted mb-0"><?= $openCount ?> open dispute(s)</p>
                </div>
            </div>

            <!-- Status Tabs -->
            <ul class="nav nav-pills mb-3">
                <?php foreach (['open', 'under_review', 'resolved', 'dismissed', ''] as $s): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $status === $s ? 'active' : '' ?>" href="<?= url('admin/disputes?status=' . $s) ?>">
                        <?= $s ? ucfirst(str_replace('_', ' ', $s)) : 'All' ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>

            <div class="card shadow-sm border-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>#</th><th>Type</th><th>Credential</th><th>Reporter</th><th>Status</th><th>Filed</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                        <?php if (empty($disputes)): ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">No disputes found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($disputes as $d): ?>
                            <tr>
                                <td>#<?= $d['id'] ?></td>
                                <td><span class="badge bg-info"><?= e($d['type']) ?></span></td>
                                <td><code><?= e($d['credential_uid'] ?? 'N/A') ?></code></td>
                                <td><?= e($d['reporter_name'] ?? 'Anonymous') ?></td>
                                <td>
                                    <span class="badge bg-<?= match($d['status']) { 'open' => 'danger', 'under_review' => 'warning', 'resolved' => 'success', 'dismissed' => 'secondary', default => 'info' } ?>">
                                        <?= e(ucfirst(str_replace('_', ' ', $d['status']))) ?>
                                    </span>
                                </td>
                                <td><?= e($d['created_at']) ?></td>
                                <td>
                                    <a href="<?= url('admin/disputes/' . $d['id']) ?>" class="btn btn-sm btn-outline-primary">View</a>
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
