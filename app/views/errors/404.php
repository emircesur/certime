<?php $title = '404 Not Found'; require APP_PATH . '/views/partials/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <span class="material-symbols-rounded" style="font-size:96px;color:var(--md-primary-light)">search_off</span>
            <h1 class="display-1 fw-bold text-muted">404</h1>
            <h4 class="fw-semibold mb-3">Page Not Found</h4>
            <p class="text-muted mb-4">The page you're looking for doesn't exist or has been moved.</p>
            <div class="d-flex gap-2 justify-content-center">
                <a href="<?= url('') ?>" class="btn btn-primary rounded-pill px-4">
                    <span class="material-symbols-rounded btn-icon">home</span>
                    Go Home
                </a>
                <a href="<?= url('verify') ?>" class="btn btn-outline-secondary rounded-pill px-4">
                    Verify a Credential
                </a>
            </div>
        </div>
    </div>
</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
