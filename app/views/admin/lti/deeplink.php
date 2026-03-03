<?php require APP_PATH . '/views/partials/header.php'; ?>

<div class="container py-4">
    <h4 class="fw-bold mb-4">Select Credential to Embed (Deep Link)</h4>
    <p class="text-muted">Choose a credential to embed in your LMS course:</p>

    <div class="row g-3">
        <?php foreach ($credentials as $c): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="fw-bold"><?= e($c['course_name']) ?></h6>
                    <p class="text-muted small mb-2"><?= e($c['student_name']) ?> — <?= e($c['issued_date']) ?></p>
                    <code class="small"><?= e($c['credential_uid']) ?></code>
                </div>
                <div class="card-footer bg-transparent">
                    <a href="<?= url('credential/' . $c['credential_uid']) ?>" class="btn btn-sm btn-primary w-100">
                        Select & Embed
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($credentials)): ?>
    <div class="text-center py-5 text-muted">
        <span class="material-symbols-rounded display-3">search_off</span>
        <p class="mt-2">No active credentials found.</p>
    </div>
    <?php endif; ?>
</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
