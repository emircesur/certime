<?php $title = '403 Forbidden'; require APP_PATH . '/views/partials/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <span class="material-symbols-rounded" style="font-size:96px;color:var(--md-error)">shield</span>
            <h1 class="display-1 fw-bold text-muted">403</h1>
            <h4 class="fw-semibold mb-3">Access Forbidden</h4>
            <p class="text-muted mb-4">You don't have permission to access this resource.</p>
            <a href="<?= url('') ?>" class="btn btn-primary rounded-pill px-4">
                <span class="material-symbols-rounded btn-icon">home</span>
                Go Home
            </a>
        </div>
    </div>
</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
