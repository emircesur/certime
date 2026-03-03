<?php require APP_PATH . '/views/partials/header.php'; ?>

<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fw-bold">
                    <span class="material-symbols-rounded" style="font-size:36px;vertical-align:-6px;color:var(--md-primary)">cloud_upload</span>
                    My Uploaded Credentials
                </h1>
                <p class="text-muted">External credentials uploaded by you — not issued by CertiMe</p>
            </div>
            <a href="<?= url('upload/create') ?>" class="btn btn-primary rounded-pill">
                <span class="material-symbols-rounded" style="font-size:18px;vertical-align:-3px">add</span>
                Upload New
            </a>
        </div>

        <?php if (empty($credentials)): ?>
        <div class="card shadow-sm border-0" style="border-radius:16px">
            <div class="card-body text-center py-5">
                <span class="material-symbols-rounded text-muted mb-3" style="font-size:64px">cloud_upload</span>
                <h4>No uploaded credentials yet</h4>
                <p class="text-muted">Upload external certificates, degrees, or badges to include them in your portfolio.</p>
                <a href="<?= url('upload/create') ?>" class="btn btn-primary rounded-pill">Upload Credential</a>
            </div>
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($credentials as $c): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm border-0" style="border-radius:16px">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-warning-subtle text-warning">
                                <span class="material-symbols-rounded" style="font-size:14px;vertical-align:-2px">upload_file</span>
                                Uploaded
                            </span>
                            <span class="badge bg-secondary-subtle text-secondary text-capitalize"><?= e($c['credential_type']) ?></span>
                        </div>
                        <h5 class="fw-semibold mt-2"><?= e($c['title']) ?></h5>
                        <p class="text-muted small mb-1">
                            <span class="material-symbols-rounded" style="font-size:14px;vertical-align:-2px">business</span>
                            <?= e($c['issuer']) ?>
                        </p>
                        <?php if ($c['issued_date']): ?>
                        <p class="text-muted small mb-1">
                            <span class="material-symbols-rounded" style="font-size:14px;vertical-align:-2px">event</span>
                            Issued: <?= date('M j, Y', strtotime($c['issued_date'])) ?>
                        </p>
                        <?php endif; ?>
                        <?php if ($c['expiration_date']): ?>
                        <p class="text-muted small mb-0">
                            <span class="material-symbols-rounded" style="font-size:14px;vertical-align:-2px">schedule</span>
                            Expires: <?= date('M j, Y', strtotime($c['expiration_date'])) ?>
                        </p>
                        <?php endif; ?>

                        <hr class="my-3">
                        <div class="d-flex gap-2">
                            <a href="<?= url('upload/' . $c['id']) ?>" class="btn btn-sm btn-outline-primary rounded-pill flex-fill">View</a>
                            <form method="POST" action="<?= url('upload/' . $c['id'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Delete this credential?')">
                                <?= csrfField() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">
                                    <span class="material-symbols-rounded" style="font-size:16px">delete</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
