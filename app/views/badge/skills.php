<?php require APP_PATH . '/views/partials/header.php'; ?>

<div class="container py-4">
    <div class="text-center mb-4">
        <h3 class="fw-bold">Skill Taxonomy</h3>
        <p class="text-muted">Browse skills organized by category and framework</p>
    </div>

    <!-- Category Filter -->
    <div class="d-flex flex-wrap gap-2 mb-4 justify-content-center">
        <a href="<?= url('directory/skills') ?>" class="btn btn-sm <?= empty($currentCategory) ? 'btn-primary' : 'btn-outline-primary' ?>">All</a>
        <?php foreach ($categories as $cat): ?>
        <a href="<?= url('directory/skills') ?>?category=<?= urlencode($cat) ?>" class="btn btn-sm <?= $currentCategory === $cat ? 'btn-primary' : 'btn-outline-primary' ?>"><?= e($cat) ?></a>
        <?php endforeach; ?>
    </div>

    <!-- Frameworks -->
    <?php if (!empty($frameworks)): ?>
    <div class="d-flex flex-wrap gap-2 mb-4 justify-content-center">
        <small class="text-muted me-2 align-self-center">Frameworks:</small>
        <?php foreach ($frameworks as $fw): ?>
        <span class="badge bg-info bg-opacity-10 text-info"><?= e($fw) ?></span>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Skills Grid -->
    <div class="row g-3">
        <?php if (empty($skills)): ?>
        <div class="col-12 text-center py-5">
            <span class="material-symbols-rounded display-3 text-muted">category</span>
            <p class="text-muted mt-2">No skills found in this category.</p>
        </div>
        <?php else: ?>
            <?php
            $grouped = [];
            foreach ($skills as $s) {
                $grouped[$s['category'] ?? 'Other'][] = $s;
            }
            ?>
            <?php foreach ($grouped as $catName => $catSkills): ?>
            <div class="col-12">
                <h5 class="fw-bold text-primary mb-3">
                    <span class="material-symbols-rounded">category</span> <?= e($catName) ?>
                </h5>
            </div>
                <?php foreach ($catSkills as $s): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="fw-bold mb-1"><?= e($s['name']) ?></h6>
                                    <code class="small"><?= e($s['code']) ?></code>
                                </div>
                                <?php if (!empty($s['framework'])): ?>
                                <span class="badge bg-secondary"><?= e($s['framework']) ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($s['description'])): ?>
                            <p class="text-muted small mt-2 mb-0"><?= e($s['description']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Link to directory -->
    <div class="text-center mt-4">
        <a href="<?= url('directory') ?>" class="btn btn-outline-primary">
            <span class="material-symbols-rounded align-middle">arrow_back</span> Back to Badge Directory
        </a>
    </div>
</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
