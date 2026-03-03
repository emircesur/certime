<?php require APP_PATH . '/views/partials/header.php'; ?>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="mb-4">
                    <a href="<?= url('upload') ?>" class="text-decoration-none text-muted">
                        <span class="material-symbols-rounded" style="font-size:18px;vertical-align:-3px">arrow_back</span>
                        Back to Uploaded Credentials
                    </a>
                </div>

                <div class="card shadow-sm border-0" style="border-radius:16px">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge bg-warning-subtle text-warning px-3 py-2">
                                <span class="material-symbols-rounded" style="font-size:16px;vertical-align:-3px">upload_file</span>
                                Uploaded — Not Issued by CertiMe
                            </span>
                            <span class="badge bg-secondary-subtle text-secondary text-capitalize"><?= e($credential['credential_type']) ?></span>
                        </div>

                        <h2 class="fw-bold"><?= e($credential['title']) ?></h2>

                        <div class="row g-3 my-3">
                            <div class="col-sm-6">
                                <div class="text-muted small">Issuing Organization</div>
                                <div class="fw-semibold"><?= e($credential['issuer']) ?></div>
                            </div>
                            <?php if ($credential['issued_date']): ?>
                            <div class="col-sm-3">
                                <div class="text-muted small">Issue Date</div>
                                <div class="fw-semibold"><?= date('M j, Y', strtotime($credential['issued_date'])) ?></div>
                            </div>
                            <?php endif; ?>
                            <?php if ($credential['expiration_date']): ?>
                            <div class="col-sm-3">
                                <div class="text-muted small">Expires</div>
                                <div class="fw-semibold"><?= date('M j, Y', strtotime($credential['expiration_date'])) ?></div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($credential['description']): ?>
                        <div class="mt-3">
                            <div class="text-muted small mb-1">Description</div>
                            <p><?= nl2br(e($credential['description'])) ?></p>
                        </div>
                        <?php endif; ?>

                        <?php if ($credential['external_url']): ?>
                        <div class="mt-3">
                            <a href="<?= e($credential['external_url']) ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill">
                                <span class="material-symbols-rounded" style="font-size:16px;vertical-align:-3px">open_in_new</span>
                                Verify at Source
                            </a>
                        </div>
                        <?php endif; ?>

                        <?php if ($credential['file_path']): ?>
                        <div class="mt-3 p-3 bg-light rounded-3">
                            <span class="material-symbols-rounded text-muted" style="font-size:20px;vertical-align:-4px">attachment</span>
                            <strong class="ms-1">Attached File</strong>
                            <?php
                            $ext = strtolower(pathinfo($credential['file_path'], PATHINFO_EXTENSION));
                            $fullPath = DATA_PATH . '/' . $credential['file_path'];
                            if (in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp']) && file_exists($fullPath)):
                            ?>
                            <div class="mt-2">
                                <img src="data:image/<?= $ext ?>;base64,<?= base64_encode(file_get_contents($fullPath)) ?>" 
                                     class="img-fluid rounded" style="max-height:400px" alt="Credential">
                            </div>
                            <?php else: ?>
                            <p class="text-muted small mt-1 mb-0">File: <?= e(basename($credential['file_path'])) ?></p>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <hr class="my-4">
                        <div class="d-flex gap-2">
                            <a href="<?= url('upload') ?>" class="btn btn-outline-secondary rounded-pill">Back</a>
                            <form method="POST" action="<?= url('upload/' . $credential['id'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Delete this credential?')">
                                <?= csrfField() ?>
                                <button type="submit" class="btn btn-outline-danger rounded-pill">
                                    <span class="material-symbols-rounded" style="font-size:18px;vertical-align:-3px">delete</span>
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
