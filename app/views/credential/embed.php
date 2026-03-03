<?php require APP_PATH . '/views/partials/header.php'; ?>

<div class="container py-4" style="max-width:700px">
    <h4 class="fw-bold mb-4">Embed Credential</h4>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body text-center">
            <h6><?= e($credential['course_name']) ?></h6>
            <p class="text-muted small">Issued by <?= e($credential['issuer_name']) ?></p>
        </div>
    </div>

    <!-- iframe embed -->
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header bg-transparent"><h6 class="mb-0">Embed as iFrame</h6></div>
        <div class="card-body">
            <textarea class="form-control font-monospace" rows="3" readonly onclick="this.select()"><?= e($iframeCode) ?></textarea>
            <button class="btn btn-sm btn-outline-primary mt-2" onclick="navigator.clipboard.writeText(document.querySelector('textarea').value);this.textContent='Copied!'">Copy Code</button>
        </div>
    </div>

    <!-- Image + link embed -->
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header bg-transparent"><h6 class="mb-0">Embed as Linked Image</h6></div>
        <div class="card-body">
            <textarea class="form-control font-monospace" rows="3" readonly onclick="this.select()" id="linkCode"><?= e($linkCode) ?></textarea>
            <button class="btn btn-sm btn-outline-primary mt-2" onclick="navigator.clipboard.writeText(document.getElementById('linkCode').value);this.textContent='Copied!'">Copy Code</button>
        </div>
    </div>

    <!-- Direct URLs -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent"><h6 class="mb-0">Direct URLs</h6></div>
        <div class="card-body">
            <div class="mb-2"><label class="form-label small">Verification Page</label><input type="text" class="form-control form-control-sm" readonly value="<?= e($verifyUrl) ?>"></div>
            <div><label class="form-label small">Badge Image</label><input type="text" class="form-control form-control-sm" readonly value="<?= e($embedUrl) ?>"></div>
        </div>
    </div>

    <div class="mt-3">
        <a href="<?= url('credential/' . $credential['credential_uid'] . '/share') ?>" class="btn btn-outline-secondary">
            <span class="material-symbols-rounded btn-icon">arrow_back</span> Back to Share Options
        </a>
    </div>
</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
