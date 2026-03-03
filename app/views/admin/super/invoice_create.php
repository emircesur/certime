<?php require APP_PATH . '/views/partials/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-2"><?php require APP_PATH . '/views/admin/partials/nav.php'; ?></div>
        <div class="col-lg-10">
            <h4 class="fw-bold mb-4">Create Manual Invoice</h4>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form method="POST" action="<?= url('admin/invoices/create') ?>">
                        <?= csrfField() ?>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Institution</label>
                                <select name="institution_id" class="form-select">
                                    <option value="">— Select institution —</option>
                                    <?php foreach ($institutions ?? [] as $i): ?>
                                    <option value="<?= $i['id'] ?>"><?= e($i['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Or Individual User</label>
                                <select name="user_id" class="form-select">
                                    <option value="">— Select user —</option>
                                    <?php foreach ($users ?? [] as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= e($u['username']) ?> (<?= e($u['email']) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Amount ($) <span class="text-danger">*</span></label>
                                <input type="number" name="amount" class="form-control" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Discount (%)</label>
                                <input type="number" name="discount_percent" class="form-control" step="0.01" min="0" max="100" value="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tax Amount ($)</label>
                                <input type="number" name="tax_amount" class="form-control" step="0.01" min="0" value="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Description</label>
                                <input type="text" name="description" class="form-control" placeholder="e.g. Enterprise Plan — Annual">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Due Date</label>
                                <input type="date" name="due_date" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="draft">Draft</option>
                                    <option value="sent">Sent</option>
                                    <option value="paid">Paid</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Internal notes..."></textarea>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <span class="material-symbols-rounded btn-icon">receipt_long</span> Create Invoice
                            </button>
                            <a href="<?= url('admin/invoices') ?>" class="btn btn-outline-secondary ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
