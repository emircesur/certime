<?php require APP_PATH . '/views/partials/header.php'; ?>

<section class="py-5">
    <div class="container">
        <div class="mb-4">
            <a href="<?= url('credential/' . $credential['credential_uid']) ?>" class="text-decoration-none text-muted">
                <span class="material-symbols-rounded" style="font-size:18px;vertical-align:-3px">arrow_back</span>
                Back to Credential
            </a>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fw-bold">
                    <span class="material-symbols-rounded" style="font-size:36px;vertical-align:-6px;color:var(--md-primary)">link</span>
                    Evidence & Attachments
                </h1>
                <p class="text-muted">Supporting evidence for: <strong><?= e($credential['course_name']) ?></strong></p>
            </div>
        </div>

        <!-- Add Evidence Form -->
        <div class="card shadow-sm border-0 mb-4" style="border-radius:16px">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="fw-semibold mb-0">
                    <span class="material-symbols-rounded" style="font-size:20px;vertical-align:-4px">add_link</span>
                    Add Evidence
                </h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="<?= url('credential/' . $credential['credential_uid'] . '/evidence') ?>" enctype="multipart/form-data">
                    <?= csrfField() ?>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Evidence Type</label>
                            <select name="type" class="form-select" id="evidenceType" onchange="toggleEvidenceFields()">
                                <option value="url">URL / Link</option>
                                <option value="github">GitHub Repository</option>
                                <option value="portfolio">Portfolio Piece</option>
                                <option value="file">File Upload</option>
                                <option value="video">Video</option>
                                <option value="publication">Publication</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Title *</label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Final Project - E-Commerce App" required>
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-8" id="urlField">
                            <label class="form-label fw-semibold">URL</label>
                            <input type="url" name="url" class="form-control" placeholder="https://github.com/user/project">
                        </div>
                        <div class="col-md-8 d-none" id="fileField">
                            <label class="form-label fw-semibold">Upload File</label>
                            <input type="file" name="evidence_file" class="form-control" accept=".pdf,.png,.jpg,.jpeg,.gif,.zip,.doc,.docx,.txt">
                            <small class="text-muted">Max 20MB. Accepted: PDF, images, documents, ZIP</small>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Brief description of this evidence..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary rounded-pill mt-3">
                        <span class="material-symbols-rounded" style="font-size:18px;vertical-align:-3px">add</span>
                        Add Evidence
                    </button>
                </form>
            </div>
        </div>

        <!-- Evidence List -->
        <div class="card shadow-sm border-0" style="border-radius:16px">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="fw-semibold mb-0">
                    <span class="material-symbols-rounded" style="font-size:20px;vertical-align:-4px">folder_open</span>
                    Linked Evidence (<?= count($evidence) ?>)
                </h5>
            </div>
            <div class="card-body p-4">
                <?php if (empty($evidence)): ?>
                <p class="text-muted text-center py-4">No evidence attached yet. Add supporting materials above.</p>
                <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php
                    $typeIcons = [
                        'url' => 'link', 'github' => 'code', 'portfolio' => 'work',
                        'file' => 'attachment', 'video' => 'play_circle', 'publication' => 'article',
                        'other' => 'description'
                    ];
                    foreach ($evidence as $ev):
                        $icon = $typeIcons[$ev['type']] ?? 'description';
                    ?>
                    <div class="list-group-item border-0 px-0 py-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="d-flex gap-3">
                                <span class="material-symbols-rounded text-primary" style="font-size:24px"><?= $icon ?></span>
                                <div>
                                    <h6 class="fw-semibold mb-1"><?= e($ev['title']) ?></h6>
                                    <span class="badge bg-primary-subtle text-primary me-2 text-capitalize"><?= e($ev['type']) ?></span>
                                    <?php if ($ev['url']): ?>
                                    <a href="<?= e($ev['url']) ?>" target="_blank" class="small text-decoration-none">
                                        <span class="material-symbols-rounded" style="font-size:14px;vertical-align:-2px">open_in_new</span>
                                        <?= e(strlen($ev['url']) > 50 ? substr($ev['url'], 0, 50) . '...' : $ev['url']) ?>
                                    </a>
                                    <?php endif; ?>
                                    <?php if ($ev['description']): ?>
                                    <p class="text-muted small mt-1 mb-0"><?= e($ev['description']) ?></p>
                                    <?php endif; ?>
                                    <small class="text-muted"><?= date('M j, Y', strtotime($ev['created_at'])) ?></small>
                                </div>
                            </div>
                            <form method="POST" action="<?= url('credential/' . $credential['credential_uid'] . '/evidence/' . $ev['id'] . '/delete') ?>" onsubmit="return confirm('Remove this evidence?')">
                                <?= csrfField() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">
                                    <span class="material-symbols-rounded" style="font-size:16px">close</span>
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
function toggleEvidenceFields() {
    const type = document.getElementById('evidenceType').value;
    const urlField = document.getElementById('urlField');
    const fileField = document.getElementById('fileField');
    if (type === 'file') {
        urlField.classList.add('d-none');
        fileField.classList.remove('d-none');
    } else {
        urlField.classList.remove('d-none');
        fileField.classList.add('d-none');
    }
}
</script>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
