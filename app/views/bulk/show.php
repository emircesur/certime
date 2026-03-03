<?php require APP_PATH . '/views/partials/header.php'; ?>

<section class="py-5">
    <div class="container">
        <div class="mb-4">
            <a href="<?= url('admin/bulk') ?>" class="text-decoration-none text-muted">
                <span class="material-symbols-rounded" style="font-size:18px;vertical-align:-3px">arrow_back</span>
                Back to Bulk Issuance
            </a>
        </div>

        <h1 class="fw-bold mb-4">
            <span class="material-symbols-rounded" style="font-size:36px;vertical-align:-6px;color:var(--md-primary)">receipt_long</span>
            Bulk Job #<?= $job['id'] ?>
        </h1>

        <div class="row g-4">
            <!-- Summary -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0" style="border-radius:16px">
                    <div class="card-body p-4">
                        <div class="row g-3 mb-4">
                            <div class="col-md-3 text-center">
                                <div class="display-6 fw-bold"><?= $job['total_rows'] ?></div>
                                <div class="text-muted small">Total Rows</div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="display-6 fw-bold text-success"><?= $job['success_count'] ?></div>
                                <div class="text-muted small">Successful</div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="display-6 fw-bold text-danger"><?= $job['error_count'] ?></div>
                                <div class="text-muted small">Errors</div>
                            </div>
                            <div class="col-md-3 text-center">
                                <?php
                                $statusColors = ['pending' => 'secondary', 'processing' => 'info', 'completed' => 'success', 'failed' => 'danger'];
                                ?>
                                <span class="badge bg-<?= $statusColors[$job['status']] ?? 'secondary' ?> fs-6 px-3 py-2">
                                    <?= ucfirst($job['status']) ?>
                                </span>
                                <div class="text-muted small mt-1">Status</div>
                            </div>
                        </div>

                        <!-- Progress bar -->
                        <?php $pct = $job['total_rows'] > 0 ? round(($job['processed_rows'] / $job['total_rows']) * 100) : 0; ?>
                        <div class="progress mb-3" style="height:8px;border-radius:4px">
                            <div class="progress-bar bg-primary" style="width:<?= $pct ?>%"></div>
                        </div>
                        <small class="text-muted"><?= $job['processed_rows'] ?> / <?= $job['total_rows'] ?> processed (<?= $pct ?>%)</small>
                    </div>
                </div>
            </div>

            <!-- Details -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0" style="border-radius:16px">
                    <div class="card-body p-4">
                        <h6 class="fw-semibold mb-3">Job Details</h6>
                        <div class="mb-2">
                            <span class="text-muted small">Filename</span><br>
                            <code><?= e($job['filename']) ?></code>
                        </div>
                        <div class="mb-2">
                            <span class="text-muted small">Created</span><br>
                            <?= date('M j, Y H:i:s', strtotime($job['created_at'])) ?>
                        </div>
                        <?php if ($job['updated_at']): ?>
                        <div class="mb-2">
                            <span class="text-muted small">Last Updated</span><br>
                            <?= date('M j, Y H:i:s', strtotime($job['updated_at'])) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($job['error_log'])): ?>
        <div class="card shadow-sm border-0 mt-4" style="border-radius:16px">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="fw-semibold mb-0 text-danger">
                    <span class="material-symbols-rounded" style="font-size:20px;vertical-align:-4px">error</span>
                    Error Log
                </h5>
            </div>
            <div class="card-body p-4">
                <pre class="bg-dark text-light p-3 rounded-3 small" style="max-height:400px;overflow-y:auto"><?= e($job['error_log']) ?></pre>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
