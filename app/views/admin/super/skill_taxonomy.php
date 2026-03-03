<?php require APP_PATH . '/views/partials/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-2"><?php require APP_PATH . '/views/admin/partials/nav.php'; ?></div>
        <div class="col-lg-10">
            <h4 class="fw-bold mb-4">Manage Skill Taxonomy</h4>

            <!-- Add Skill -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h6 class="mb-3">Add New Skill</h6>
                    <form method="POST" action="<?= url('admin/skills/add') ?>">
                        <?= csrfField() ?>
                        <div class="row g-2">
                            <div class="col-md-2"><input type="text" name="code" class="form-control" placeholder="SKILL_CODE" required></div>
                            <div class="col-md-3"><input type="text" name="name" class="form-control" placeholder="Skill Name" required></div>
                            <div class="col-md-2"><input type="text" name="category" class="form-control" placeholder="Category"></div>
                            <div class="col-md-2">
                                <select name="framework" class="form-select">
                                    <option value="custom">Custom</option>
                                    <option value="esco">ESCO</option>
                                    <option value="lightcast">Lightcast</option>
                                    <option value="onet">O*NET</option>
                                </select>
                            </div>
                            <div class="col-md-2"><input type="text" name="description" class="form-control" placeholder="Description"></div>
                            <div class="col-md-1"><button type="submit" class="btn btn-primary w-100">Add</button></div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Link Skill to Credential -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h6 class="mb-3">Link Skill to Credential</h6>
                    <form method="POST" action="<?= url('admin/skills/link') ?>">
                        <?= csrfField() ?>
                        <div class="row g-2">
                            <div class="col-md-4"><input type="text" name="credential_uid" class="form-control" placeholder="Credential UID" required></div>
                            <div class="col-md-4">
                                <select name="skill_code" class="form-select" required>
                                    <option value="">Select skill...</option>
                                    <?php foreach ($skills as $s): ?>
                                    <option value="<?= e($s['code']) ?>"><?= e($s['name']) ?> (<?= e($s['code']) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4"><button type="submit" class="btn btn-success w-100">Link Skill</button></div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light"><tr><th>Code</th><th>Name</th><th>Category</th><th>Framework</th></tr></thead>
                        <tbody>
                        <?php foreach ($skills as $s): ?>
                        <tr>
                            <td><code><?= e($s['code']) ?></code></td>
                            <td><?= e($s['name']) ?></td>
                            <td><?= e($s['category'] ?? '') ?></td>
                            <td><span class="badge bg-info"><?= e($s['framework'] ?? 'custom') ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
