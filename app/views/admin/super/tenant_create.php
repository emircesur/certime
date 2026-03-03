<?php require APP_PATH . '/views/partials/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-2"><?php require APP_PATH . '/views/admin/partials/nav.php'; ?></div>
        <div class="col-lg-10">
            <h4 class="fw-bold mb-4">Onboard New Institution</h4>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form method="POST" action="<?= url('admin/tenants/create') ?>">
                        <?= csrfField() ?>

                        <div class="mb-3">
                            <label class="form-label">Institution Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. MIT, Stanford">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Owner Email <span class="text-danger">*</span></label>
                            <input type="email" name="owner_email" class="form-control" required placeholder="admin@university.edu">
                            <div class="form-text">Must be an existing registered user.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Billing Email</label>
                            <input type="email" name="billing_email" class="form-control" placeholder="billing@university.edu">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Plan</label>
                            <select name="plan_id" class="form-select">
                                <option value="">— No plan —</option>
                                <?php foreach ($plans ?? [] as $plan): ?>
                                <option value="<?= $plan['id'] ?>"><?= e($plan['name']) ?> — $<?= $plan['price'] ?>/mo</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <span class="material-symbols-rounded btn-icon">add</span> Create Institution
                        </button>
                        <a href="<?= url('admin/tenants') ?>" class="btn btn-outline-secondary ms-2">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
