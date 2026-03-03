<?php require APP_PATH . '/views/partials/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-2"><?php require APP_PATH . '/views/admin/partials/nav.php'; ?></div>
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">Dispute #<?= $dispute['id'] ?></h4>
                <a href="<?= url('admin/disputes') ?>" class="btn btn-outline-secondary rounded-pill">
                    <span class="material-symbols-rounded btn-icon">arrow_back</span> Back
                </a>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-sm-3">Type</dt>
                                <dd class="col-sm-9"><span class="badge bg-info"><?= e($dispute['type']) ?></span></dd>
                                <dt class="col-sm-3">Status</dt>
                                <dd class="col-sm-9">
                                    <span class="badge bg-<?= match($dispute['status']) { 'open' => 'danger', 'under_review' => 'warning', 'resolved' => 'success', default => 'secondary' } ?>">
                                        <?= e(ucfirst(str_replace('_', ' ', $dispute['status']))) ?>
                                    </span>
                                </dd>
                                <dt class="col-sm-3">Credential</dt>
                                <dd class="col-sm-9">
                                    <?php if ($dispute['credential_uid']): ?>
                                        <a href="<?= url('credential/' . $dispute['credential_uid']) ?>"><code><?= e($dispute['credential_uid']) ?></code></a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </dd>
                                <dt class="col-sm-3">Reporter</dt>
                                <dd class="col-sm-9"><?= e($dispute['reporter_email'] ?? 'Anonymous') ?></dd>
                                <dt class="col-sm-3">Filed</dt>
                                <dd class="col-sm-9"><?= e($dispute['created_at']) ?></dd>
                                <dt class="col-sm-3">Description</dt>
                                <dd class="col-sm-9"><?= nl2br(e($dispute['description'] ?? '')) ?></dd>
                                <?php if (!empty($dispute['resolution_notes'])): ?>
                                <dt class="col-sm-3">Resolution</dt>
                                <dd class="col-sm-9"><?= nl2br(e($dispute['resolution_notes'])) ?></dd>
                                <?php endif; ?>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-transparent"><h6 class="mb-0">Update Status</h6></div>
                        <div class="card-body">
                            <form method="POST" action="<?= url('admin/disputes/' . $dispute['id'] . '/update') ?>">
                                <?= csrfField() ?>
                                <div class="mb-3">
                                    <select name="status" class="form-select">
                                        <option value="open" <?= $dispute['status'] === 'open' ? 'selected' : '' ?>>Open</option>
                                        <option value="under_review" <?= $dispute['status'] === 'under_review' ? 'selected' : '' ?>>Under Review</option>
                                        <option value="resolved" <?= $dispute['status'] === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                                        <option value="dismissed" <?= $dispute['status'] === 'dismissed' ? 'selected' : '' ?>>Dismissed</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <textarea name="resolution_notes" class="form-control" rows="4" placeholder="Resolution notes..."><?= e($dispute['resolution_notes'] ?? '') ?></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Update Dispute</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
