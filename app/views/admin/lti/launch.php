<?php require APP_PATH . '/views/partials/header.php'; ?>

<div class="container py-4">
    <h4 class="fw-bold mb-4">LTI Tool Launch</h4>
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <p>Welcome, <strong><?= e($context['name'] ?? 'LTI User') ?></strong></p>
            <p class="text-muted">You've launched CertiMe from your LMS.</p>

            <div class="row g-3 mt-3">
                <div class="col-md-6">
                    <a href="<?= url('directory') ?>" class="btn btn-primary w-100">
                        <span class="material-symbols-rounded btn-icon">search</span> Browse Badge Directory
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="<?= url('verify') ?>" class="btn btn-outline-primary w-100">
                        <span class="material-symbols-rounded btn-icon">fact_check</span> Verify a Credential
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
