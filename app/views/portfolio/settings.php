<?php require APP_PATH . '/views/partials/header.php'; ?>

<div class="container py-4" style="max-width:700px">
    <h3 class="fw-bold mb-4">
        <span class="material-symbols-rounded align-middle">settings</span> Portfolio Settings
    </h3>

    <?php if ($flash = flash('success')): ?>
    <div class="alert alert-success"><?= e($flash) ?></div>
    <?php endif; ?>
    <?php if ($flash = flash('error')): ?>
    <div class="alert alert-danger"><?= e($flash) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= url('portfolio/settings') ?>">
        <?= csrfField() ?>

        <!-- Public Toggle -->
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="portfolio_public" name="portfolio_public" value="1" <?= !empty($user['portfolio_public']) ? 'checked' : '' ?>>
                    <label class="form-check-label fw-bold" for="portfolio_public">Make portfolio public</label>
                </div>
                <small class="text-muted">When enabled, anyone with your link can view your credentials portfolio.</small>
            </div>
        </div>

        <!-- Slug -->
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <label class="form-label fw-bold">Portfolio URL Slug</label>
                <div class="input-group">
                    <span class="input-group-text"><?= e(rtrim(absUrl(''), '/')) ?>/p/</span>
                    <input type="text" name="portfolio_slug" class="form-control" value="<?= e($user['portfolio_slug'] ?? '') ?>" placeholder="your-name" pattern="[a-z0-9\-]+" title="Lower case letters, numbers and hyphens only">
                </div>
                <small class="text-muted">Choose a unique URL for your public portfolio. Only lower-case letters, numbers, and hyphens.</small>
            </div>
        </div>

        <!-- Theme -->
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <label class="form-label fw-bold">Portfolio Theme</label>
                <div class="row g-2">
                    <?php
                    $themes = [
                        'default'  => ['icon' => 'light_mode', 'label' => 'Default'],
                        'dark'     => ['icon' => 'dark_mode', 'label' => 'Dark'],
                        'minimal'  => ['icon' => 'format_align_left', 'label' => 'Minimal'],
                        'academic' => ['icon' => 'school', 'label' => 'Academic'],
                    ];
                    $current = $user['portfolio_theme'] ?? 'default';
                    ?>
                    <?php foreach ($themes as $key => $t): ?>
                    <div class="col-3">
                        <input type="radio" class="btn-check" name="portfolio_theme" id="theme_<?= $key ?>" value="<?= $key ?>" <?= $current === $key ? 'checked' : '' ?>>
                        <label class="btn btn-outline-primary w-100 d-flex flex-column align-items-center py-3" for="theme_<?= $key ?>">
                            <span class="material-symbols-rounded mb-1"><?= $t['icon'] ?></span>
                            <small><?= $t['label'] ?></small>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Social Links -->
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <label class="form-label fw-bold">Social Links</label>
                <?php $social = json_decode($user['social_links'] ?? '{}', true) ?: []; ?>
                <div class="mb-2">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">LinkedIn</span>
                        <input type="url" name="social_linkedin" class="form-control" value="<?= e($social['linkedin'] ?? '') ?>" placeholder="https://linkedin.com/in/...">
                    </div>
                </div>
                <div class="mb-2">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">Twitter</span>
                        <input type="url" name="social_twitter" class="form-control" value="<?= e($social['twitter'] ?? '') ?>" placeholder="https://twitter.com/...">
                    </div>
                </div>
                <div class="mb-2">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">GitHub</span>
                        <input type="url" name="social_github" class="form-control" value="<?= e($social['github'] ?? '') ?>" placeholder="https://github.com/...">
                    </div>
                </div>
                <div class="mb-2">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">Website</span>
                        <input type="url" name="social_website" class="form-control" value="<?= e($social['website'] ?? '') ?>" placeholder="https://...">
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">
            <span class="material-symbols-rounded align-middle me-1">save</span> Save Settings
        </button>

        <?php if (!empty($user['portfolio_slug']) && !empty($user['portfolio_public'])): ?>
        <a href="<?= url('p/' . $user['portfolio_slug']) ?>" class="btn btn-outline-secondary ms-2" target="_blank">
            <span class="material-symbols-rounded align-middle me-1">open_in_new</span> View Portfolio
        </a>
        <?php endif; ?>
    </form>
</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
