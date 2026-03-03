<?php require APP_PATH . '/views/partials/header.php'; ?>

<div class="container py-4">
    <div class="text-center mb-4">
        <h3 class="fw-bold">Badge Directory</h3>
        <p class="text-muted">Discover available digital credentials and skill badges</p>
    </div>

    <!-- Search -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body py-3">
            <form method="GET" action="<?= url('directory') ?>" class="row g-2 align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><span class="material-symbols-rounded" style="font-size:20px">search</span></span>
                        <input type="text" name="q" class="form-control" placeholder="Search badges..." value="<?= e($query) ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= e($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Featured Badges -->
    <?php if (!empty($featured)): ?>
    <h5 class="fw-bold mb-3">
        <span class="material-symbols-rounded text-warning">star</span> Featured Badges
    </h5>
    <div class="row g-3 mb-4">
        <?php foreach ($featured as $b): ?>
        <div class="col-md-4 col-lg-2">
            <div class="card shadow-sm border-0 h-100 text-center">
                <div class="card-body">
                    <?php if (!empty($b['image_url'])): ?>
                    <img src="<?= e($b['image_url']) ?>" alt="<?= e($b['name']) ?>" class="rounded mb-2" style="width:64px;height:64px;object-fit:cover">
                    <?php else: ?>
                    <span class="material-symbols-rounded display-6 text-primary mb-2">workspace_premium</span>
                    <?php endif; ?>
                    <h6 class="fw-bold small"><?= e($b['name']) ?></h6>
                    <p class="text-muted small mb-0"><?= e($b['issuer_name'] ?? '') ?></p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Results -->
    <div class="row g-3">
        <?php if (empty($badges)): ?>
        <div class="col-12 text-center py-5">
            <span class="material-symbols-rounded display-3 text-muted">search_off</span>
            <p class="text-muted mt-2">No badges found. Try adjusting your search.</p>
        </div>
        <?php else: ?>
            <?php foreach ($badges as $b): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start gap-3">
                            <?php if (!empty($b['image_url'])): ?>
                            <img src="<?= e($b['image_url']) ?>" alt="" class="rounded" style="width:48px;height:48px;object-fit:cover">
                            <?php else: ?>
                            <span class="material-symbols-rounded text-primary" style="font-size:48px">workspace_premium</span>
                            <?php endif; ?>
                            <div>
                                <h6 class="fw-bold mb-1"><?= e($b['name']) ?></h6>
                                <p class="text-muted small mb-1"><?= e($b['issuer_name'] ?? '') ?></p>
                                <?php if (!empty($b['category'])): ?>
                                <span class="badge bg-light text-dark"><?= e($b['category']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if (!empty($b['description'])): ?>
                        <p class="small text-muted mt-2 mb-0"><?= e(substr($b['description'], 0, 150)) ?></p>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($b['criteria_url'])): ?>
                    <div class="card-footer bg-transparent">
                        <a href="<?= e($b['criteria_url']) ?>" class="btn btn-sm btn-outline-primary" target="_blank">View Criteria</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
