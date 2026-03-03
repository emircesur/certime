<?php $title = 'PDF Keys Missing'; require APP_PATH . '/views/partials/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <span class="material-symbols-rounded" style="font-size:96px;color:var(--md-warning)">vpn_key_off</span>
            <h3 class="fw-bold mt-3">PDF Signing Keys Not Found</h3>
            <p class="text-muted mb-4">
                PDF certificate generation requires X.509 signing keys. 
                An administrator needs to generate or upload them first.
            </p>
            <?php if (isStaff()): ?>
                <a href="<?= url('admin/keys') ?>" class="btn btn-primary rounded-pill px-4">
                    <span class="material-symbols-rounded btn-icon">vpn_key</span>
                    Manage Keys
                </a>
            <?php else: ?>
                <a href="<?= url('portfolio') ?>" class="btn btn-primary rounded-pill px-4">
                    <span class="material-symbols-rounded btn-icon">arrow_back</span>
                    Back to Portfolio
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
