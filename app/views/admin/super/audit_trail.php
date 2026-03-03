<?php require APP_PATH . '/views/partials/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-2"><?php require APP_PATH . '/views/admin/partials/nav.php'; ?></div>
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1">Global Audit Trail</h4>
                    <p class="text-muted mb-0"><?= number_format($total) ?> total events</p>
                </div>
            </div>

            <!-- Filter -->
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body py-2">
                    <form method="GET" action="<?= url('admin/audit-trail') ?>" class="row g-2 align-items-center">
                        <div class="col-md-8">
                            <input type="text" name="filter" class="form-control" placeholder="Filter by action or details..." value="<?= e($filter) ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Search</button>
                        </div>
                        <div class="col-md-2">
                            <a href="<?= url('admin/audit-trail') ?>" class="btn btn-outline-secondary w-100">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>Time</th><th>User</th><th>Action</th><th>Details</th><th>IP</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="text-nowrap small"><?= e($log['timestamp']) ?></td>
                            <td><?= e($log['username'] ?? 'System') ?></td>
                            <td><code class="small"><?= e($log['action']) ?></code></td>
                            <td class="small text-muted" style="max-width:400px;overflow:hidden;text-overflow:ellipsis"><?= e($log['details'] ?? '') ?></td>
                            <td class="small"><code><?= e($log['ip_address'] ?? '') ?></code></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($logs)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No audit events found.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <nav class="mt-3">
                <ul class="pagination justify-content-center">
                    <?php for ($i = max(1, $page - 3); $i <= min($totalPages, $page + 3); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="<?= url('admin/audit-trail?page=' . $i . ($filter ? '&filter=' . urlencode($filter) : '')) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
