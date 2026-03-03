<?php require APP_PATH . '/views/partials/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-2"><?php require APP_PATH . '/views/admin/partials/nav.php'; ?></div>
        <div class="col-lg-10">
            <h4 class="fw-bold mb-4">Impersonation Audit Log</h4>

            <div class="card shadow-sm border-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Admin</th><th>Target User</th><th>Reason</th><th>IP</th><th>Started</th><th>Ended</th></tr>
                        </thead>
                        <tbody>
                        <?php if (empty($logs)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">No impersonation events.</td></tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><strong><?= e($log['admin_name']) ?></strong></td>
                                <td><?= e($log['target_name']) ?></td>
                                <td class="text-muted"><?= e($log['reason']) ?></td>
                                <td><code><?= e($log['ip_address']) ?></code></td>
                                <td><?= e($log['started_at']) ?></td>
                                <td><?= $log['ended_at'] ? e($log['ended_at']) : '<span class="badge bg-warning">Active</span>' ?></td>
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
