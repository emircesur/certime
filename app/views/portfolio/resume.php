<?php require APP_PATH . '/views/partials/header.php'; ?>

<div class="container py-4">
    <h3 class="fw-bold mb-4">
        <span class="material-symbols-rounded align-middle">description</span> Digital Resume
    </h3>

    <div class="row g-4">
        <!-- Preview -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <!-- Header -->
                    <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width:56px;height:56px">
                            <span class="material-symbols-rounded text-white" style="font-size:28px">person</span>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0"><?= e($user['name'] ?? '') ?></h4>
                            <p class="text-muted mb-0"><?= e($user['email'] ?? '') ?></p>
                        </div>
                    </div>

                    <!-- Skills -->
                    <?php if (!empty($skills)): ?>
                    <h6 class="fw-bold text-primary mb-2">
                        <span class="material-symbols-rounded align-middle">psychology</span> Verified Skills
                    </h6>
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <?php foreach ($skills as $skill): ?>
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2"><?= e($skill) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Credentials -->
                    <?php if (!empty($credentials)): ?>
                    <h6 class="fw-bold text-primary mb-2">
                        <span class="material-symbols-rounded align-middle">workspace_premium</span> Credentials (<?= count($credentials) ?>)
                    </h6>
                    <div class="list-group list-group-flush">
                        <?php foreach ($credentials as $cred): ?>
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1 fw-semibold"><?= e($cred['title']) ?></h6>
                                    <p class="text-muted small mb-1"><?= e($cred['description'] ?? '') ?></p>
                                    <small class="text-muted">Issued: <?= date('M j, Y', strtotime($cred['created_at'])) ?></small>
                                    <?php if (!empty($cred['expires_at'])): ?>
                                    <small class="text-muted ms-2">Expires: <?= date('M j, Y', strtotime($cred['expires_at'])) ?></small>
                                    <?php endif; ?>
                                </div>
                                <span class="badge bg-<?= $cred['status'] === 'issued' ? 'success' : ($cred['status'] === 'revoked' ? 'danger' : 'warning') ?>"><?= e(ucfirst($cred['status'])) ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <?php if (empty($credentials)): ?>
                    <div class="text-center py-5 text-muted">
                        <span class="material-symbols-rounded display-4">description</span>
                        <p class="mt-2">No credentials to display in your resume yet.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Export Actions -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent">
                    <h6 class="fw-bold mb-0">Export Options</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= url('resume/pdf') ?>" class="btn btn-primary">
                            <span class="material-symbols-rounded align-middle me-1">picture_as_pdf</span> Download PDF
                        </a>
                        <a href="<?= url('resume/json') ?>" class="btn btn-outline-primary">
                            <span class="material-symbols-rounded align-middle me-1">code</span> Export Verifiable Presentation (JSON-LD)
                        </a>
                    </div>
                    <hr>
                    <p class="text-muted small mb-0">
                        <span class="material-symbols-rounded align-middle" style="font-size:16px">info</span>
                        The Verifiable Presentation export includes Ed25519 cryptographic proof for machine-verifiable credentials.
                    </p>
                </div>
            </div>

            <!-- Portfolio Link -->
            <div class="card shadow-sm border-0 mt-3">
                <div class="card-body text-center">
                    <span class="material-symbols-rounded display-6 text-primary">share</span>
                    <h6 class="fw-bold mt-2">Public Portfolio</h6>
                    <p class="small text-muted">Share your credentials publicly</p>
                    <a href="<?= url('portfolio/settings') ?>" class="btn btn-sm btn-outline-primary">Configure Portfolio</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
