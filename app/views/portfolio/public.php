<?php require APP_PATH . '/views/partials/header.php'; ?>

<?php
$themeClasses = [
    'default' => '',
    'dark'    => 'bg-dark text-light',
    'minimal' => 'bg-light',
    'academic' => '',
];
$themeClass = $themeClasses[$portfolio['portfolio_theme'] ?? 'default'] ?? '';
$socialLinks = json_decode($portfolio['social_links'] ?? '{}', true) ?: [];
?>

<div class="<?= $themeClass ?>" style="min-height:80vh">
<div class="container py-5">
    <!-- Profile Header -->
    <div class="text-center mb-5">
        <div class="bg-primary bg-gradient rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:80px;height:80px">
            <span class="material-symbols-rounded text-white" style="font-size:40px">person</span>
        </div>
        <h2 class="fw-bold"><?= e($portfolio['name'] ?? 'Unknown') ?></h2>
        <?php if (!empty($socialLinks)): ?>
        <div class="d-flex gap-3 justify-content-center mt-2">
            <?php if (!empty($socialLinks['linkedin'])): ?>
            <a href="<?= e($socialLinks['linkedin']) ?>" class="text-decoration-none" target="_blank" title="LinkedIn">🔗 LinkedIn</a>
            <?php endif; ?>
            <?php if (!empty($socialLinks['twitter'])): ?>
            <a href="<?= e($socialLinks['twitter']) ?>" class="text-decoration-none" target="_blank" title="Twitter">🐦 Twitter</a>
            <?php endif; ?>
            <?php if (!empty($socialLinks['website'])): ?>
            <a href="<?= e($socialLinks['website']) ?>" class="text-decoration-none" target="_blank" title="Website">🌐 Website</a>
            <?php endif; ?>
            <?php if (!empty($socialLinks['github'])): ?>
            <a href="<?= e($socialLinks['github']) ?>" class="text-decoration-none" target="_blank" title="GitHub">💻 GitHub</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Skills Summary -->
    <?php if (!empty($skills)): ?>
    <div class="text-center mb-4">
        <h5 class="fw-bold mb-3">Verified Skills</h5>
        <div class="d-flex flex-wrap gap-2 justify-content-center">
            <?php foreach ($skills as $skill): ?>
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 fs-6"><?= e($skill) ?></span>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Credentials -->
    <h5 class="fw-bold mb-3 text-center">Credentials (<?= count($credentials) ?>)</h5>
    <div class="row g-3">
        <?php if (empty($credentials)): ?>
        <div class="col-12 text-center py-5">
            <span class="material-symbols-rounded display-3 text-muted">workspace_premium</span>
            <p class="text-muted mt-2">No public credentials yet.</p>
        </div>
        <?php else: ?>
            <?php foreach ($credentials as $cred): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="material-symbols-rounded text-primary">workspace_premium</span>
                            <span class="badge bg-<?= $cred['status'] === 'issued' ? 'success' : 'secondary' ?>"><?= e(ucfirst($cred['status'])) ?></span>
                        </div>
                        <h6 class="fw-bold"><?= e($cred['title']) ?></h6>
                        <p class="text-muted small"><?= e(substr($cred['description'] ?? '', 0, 120)) ?></p>
                        <small class="text-muted">Issued: <?= date('M j, Y', strtotime($cred['created_at'])) ?></small>
                    </div>
                    <div class="card-footer bg-transparent">
                        <a href="<?= url('credential/' . $cred['uid']) ?>" class="btn btn-sm btn-outline-primary w-100">
                            <span class="material-symbols-rounded align-middle" style="font-size:16px">verified</span> Verify
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Powered by -->
    <div class="text-center mt-5 pt-4 border-top">
        <small class="text-muted">Powered by <strong>CertiMe</strong> — Verified Digital Credentials</small>
    </div>
</div>
</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
