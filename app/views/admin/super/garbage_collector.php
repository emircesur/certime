<?php require APP_PATH . '/views/partials/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-2"><?php require APP_PATH . '/views/admin/partials/nav.php'; ?></div>
        <div class="col-lg-10">
            <h4 class="fw-bold mb-4">Orphaned Data Garbage Collector</h4>

            <!-- Stats Cards -->
            <div class="row g-3 mb-4">
                <div class="col-sm-6 col-xl-3">
                    <div class="card shadow-sm border-0 text-center p-3">
                        <div class="display-6 fw-bold text-warning"><?= $stats['old_sessions'] ?? 0 ?></div>
                        <div class="text-muted small">Expired Sessions</div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card shadow-sm border-0 text-center p-3">
                        <div class="display-6 fw-bold text-danger"><?= $stats['orphaned_portfolios'] ?? 0 ?></div>
                        <div class="text-muted small">Orphaned Portfolios</div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card shadow-sm border-0 text-center p-3">
                        <div class="display-6 fw-bold text-info"><?= $stats['tmp_files'] ?? 0 ?></div>
                        <div class="text-muted small">Temp Files</div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card shadow-sm border-0 text-center p-3">
                        <div class="display-6 fw-bold text-secondary"><?= $stats['unverified_accounts'] ?? 0 ?></div>
                        <div class="text-muted small">Stale Accounts</div>
                    </div>
                </div>
            </div>

            <!-- Run Buttons -->
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="mb-3">Run Garbage Collection</h6>
                    <div class="row g-2">
                        <div class="col-auto">
                            <form method="POST" action="<?= url('admin/garbage-collector/run') ?>">
                                <?= csrfField() ?>
                                <input type="hidden" name="target" value="all">
                                <button class="btn btn-danger" onclick="return confirm('This will clean all orphaned data. Continue?')">
                                    <span class="material-symbols-rounded btn-icon">delete_sweep</span> Clean All
                                </button>
                            </form>
                        </div>
                        <div class="col-auto">
                            <form method="POST" action="<?= url('admin/garbage-collector/run') ?>" class="d-inline">
                                <?= csrfField() ?>
                                <input type="hidden" name="target" value="sessions">
                                <button class="btn btn-outline-warning">Clean Sessions</button>
                            </form>
                        </div>
                        <div class="col-auto">
                            <form method="POST" action="<?= url('admin/garbage-collector/run') ?>" class="d-inline">
                                <?= csrfField() ?>
                                <input type="hidden" name="target" value="portfolios">
                                <button class="btn btn-outline-danger">Clean Portfolios</button>
                            </form>
                        </div>
                        <div class="col-auto">
                            <form method="POST" action="<?= url('admin/garbage-collector/run') ?>" class="d-inline">
                                <?= csrfField() ?>
                                <input type="hidden" name="target" value="tmp">
                                <button class="btn btn-outline-info">Clean Tmp Files</button>
                            </form>
                        </div>
                        <div class="col-auto">
                            <form method="POST" action="<?= url('admin/garbage-collector/run') ?>" class="d-inline">
                                <?= csrfField() ?>
                                <input type="hidden" name="target" value="otp">
                                <button class="btn btn-outline-secondary">Clean Expired OTPs</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
