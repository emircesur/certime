<?php require APP_PATH . '/views/partials/header.php'; ?>

<div class="container py-4" style="max-width:600px">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Share Credential</h4>
        <a href="<?= url('credential/' . $credential['credential_uid']) ?>" class="btn btn-outline-secondary rounded-pill btn-sm">
            <span class="material-symbols-rounded btn-icon">arrow_back</span> Back
        </a>
    </div>

    <!-- Credential Preview -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body text-center">
            <span class="material-symbols-rounded display-4 text-primary">workspace_premium</span>
            <h5 class="fw-bold mt-2"><?= e($credential['course_name']) ?></h5>
            <p class="text-muted mb-1">Issued by <?= e($credential['issuer_name']) ?></p>
            <?php if (($credential['share_count'] ?? 0) > 0): ?>
            <span class="badge bg-light text-dark">Shared <?= $shareCount ?> time(s)</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Share Buttons -->
    <div class="row g-3">
        <?php foreach ($platforms as $p): ?>
        <div class="col-6 col-md-4">
            <a href="<?= url('share/' . $credential['credential_uid'] . '/' . $p['id']) ?>" 
               class="btn w-100 d-flex align-items-center justify-content-center gap-2"
               style="background:<?= $p['color'] ?>;color:#fff;border:none"
               <?= $p['id'] !== 'embed' ? 'target="_blank"' : '' ?>>
                <span class="material-symbols-rounded" style="font-size:20px"><?= $p['icon'] ?></span>
                <?= e($p['name']) ?>
            </a>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Copy Link -->
    <div class="card shadow-sm border-0 mt-4">
        <div class="card-body">
            <label class="form-label">Direct Link</label>
            <div class="input-group">
                <input type="text" class="form-control" value="<?= e($credUrl) ?>" id="credLink" readonly>
                <button class="btn btn-outline-primary" onclick="navigator.clipboard.writeText(document.getElementById('credLink').value);this.textContent='Copied!'">Copy</button>
            </div>
        </div>
    </div>
</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
