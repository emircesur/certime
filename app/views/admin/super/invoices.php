<?php require APP_PATH . '/views/partials/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-2"><?php require APP_PATH . '/views/admin/partials/nav.php'; ?></div>
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1">Invoice & Manual Payment Override</h4>
                    <p class="text-muted mb-0">Total revenue: $<?= number_format($totalRevenue ?? 0, 2) ?></p>
                </div>
                <a href="<?= url('admin/invoices/create') ?>" class="btn btn-primary rounded-pill">
                    <span class="material-symbols-rounded btn-icon">add</span> Create Invoice
                </a>
            </div>

            <div class="card shadow-sm border-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Invoice #</th><th>Institution/User</th><th>Amount</th><th>Status</th><th>Due</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                        <?php if (empty($invoices)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">No invoices yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($invoices as $inv): ?>
                            <tr>
                                <td><code><?= e($inv['invoice_number']) ?></code></td>
                                <td><?= e($inv['institution_name'] ?? $inv['username'] ?? 'N/A') ?></td>
                                <td class="fw-semibold">$<?= number_format($inv['total_amount'], 2) ?></td>
                                <td>
                                    <span class="badge bg-<?= match($inv['status']) { 'paid' => 'success', 'sent' => 'primary', 'overdue' => 'danger', 'draft' => 'secondary', 'cancelled' => 'dark', default => 'info' } ?>">
                                        <?= e(ucfirst($inv['status'])) ?>
                                    </span>
                                </td>
                                <td><?= e($inv['due_date'] ?? 'N/A') ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <?php if ($inv['status'] !== 'paid'): ?>
                                        <form method="POST" action="<?= url('admin/invoices/' . $inv['id'] . '/status') ?>" class="d-inline">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="status" value="paid">
                                            <button class="btn btn-outline-success" title="Mark Paid">
                                                <span class="material-symbols-rounded" style="font-size:16px">check</span>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                        <?php if ($inv['status'] === 'draft'): ?>
                                        <form method="POST" action="<?= url('admin/invoices/' . $inv['id'] . '/status') ?>" class="d-inline">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="status" value="sent">
                                            <button class="btn btn-outline-primary" title="Mark Sent">
                                                <span class="material-symbols-rounded" style="font-size:16px">send</span>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                        <form method="POST" action="<?= url('admin/invoices/' . $inv['id'] . '/status') ?>" class="d-inline">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="status" value="cancelled">
                                            <button class="btn btn-outline-danger" title="Cancel">
                                                <span class="material-symbols-rounded" style="font-size:16px">close</span>
                                            </button>
                                        </form>
                                    </div>
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
