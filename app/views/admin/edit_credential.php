<?php require APP_PATH . '/views/partials/header.php'; ?>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="mb-4">
                    <a href="<?= url('admin/credentials') ?>" class="text-decoration-none text-muted">
                        <span class="material-symbols-rounded" style="font-size:18px;vertical-align:-3px">arrow_back</span>
                        Back to Credentials
                    </a>
                </div>

                <div class="card shadow-sm border-0" style="border-radius:16px">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h2 class="fw-bold mb-1">
                            <span class="material-symbols-rounded" style="font-size:28px;vertical-align:-5px;color:var(--md-primary)">edit</span>
                            Edit Credential
                        </h2>
                        <p class="text-muted small mb-0">UID: <code><?= e($credential['credential_uid']) ?></code></p>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" action="<?= url('admin/credentials/' . $credential['credential_uid'] . '/edit') ?>">
                            <?= csrfField() ?>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Credential Title</label>
                                <input type="text" name="course_name" class="form-control" value="<?= e($credential['course_name']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" class="form-control" rows="3"><?= e($credential['description']) ?></textarea>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Category</label>
                                    <input type="text" name="category" class="form-control" value="<?= e($credential['category'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Credential Type</label>
                                    <select name="credential_type" class="form-select">
                                        <?php foreach (['certificate','degree','diploma','license','badge','certification','course','micro-credential'] as $t): ?>
                                        <option value="<?= $t ?>" <?= ($credential['credential_type'] ?? '') === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Credit Hours</label>
                                    <input type="number" name="credit_hours" class="form-control" step="0.5" value="<?= e($credential['credit_hours'] ?? 0) ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Skills (comma-separated)</label>
                                <input type="text" name="skills" class="form-control" value="<?= e($credential['skills'] ?? '') ?>" placeholder="HTML, CSS, JavaScript">
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Expiration Date</label>
                                    <input type="date" name="expiration_date" class="form-control" value="<?= e($credential['expiration_date'] ?? '') ?>">
                                    <small class="text-muted">Leave blank for no expiration</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">PDF Template</label>
                                    <select name="pdf_template" class="form-select">
                                        <?php foreach (['classic' => 'Classic', 'modern' => 'Modern', 'minimal' => 'Minimal', 'professional' => 'Professional', 'elegant' => 'Elegant'] as $k => $v): ?>
                                        <option value="<?= $k ?>" <?= ($credential['pdf_template'] ?? 'classic') === $k ? 'selected' : '' ?>><?= $v ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary rounded-pill px-4">
                                    <span class="material-symbols-rounded" style="font-size:18px;vertical-align:-3px">save</span>
                                    Save Changes
                                </button>
                                <a href="<?= url('admin/credentials') ?>" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
