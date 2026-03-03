<?php require APP_PATH . '/views/partials/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-2"><?php require APP_PATH . '/views/admin/partials/nav.php'; ?></div>
        <div class="col-lg-10">
            <h4 class="fw-bold mb-4">Feature Flag Controller</h4>

            <!-- Global Flags -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-transparent"><h6 class="mb-0">Global Feature Flags</h6></div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light"><tr><th>Flag</th><th>Description</th><th>Status</th><th>Toggle</th></tr></thead>
                        <tbody>
                        <?php foreach ($globalFlags as $flag): ?>
                        <tr>
                            <td><code><?= e($flag['name']) ?></code></td>
                            <td class="text-muted"><?= e($flag['description'] ?? '') ?></td>
                            <td>
                                <span class="badge bg-<?= $flag['is_enabled'] ? 'success' : 'secondary' ?>">
                                    <?= $flag['is_enabled'] ? 'ON' : 'OFF' ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="<?= url('admin/feature-flags/' . $flag['id'] . '/toggle') ?>" class="d-inline">
                                    <?= csrfField() ?>
                                    <button class="btn btn-sm btn-outline-<?= $flag['is_enabled'] ? 'danger' : 'success' ?>">
                                        <?= $flag['is_enabled'] ? 'Disable' : 'Enable' ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Override per Institution -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent"><h6 class="mb-0">Per-Institution Override</h6></div>
                <div class="card-body">
                    <form method="POST" action="<?= url('admin/feature-flags/institution') ?>">
                        <?= csrfField() ?>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Institution</label>
                                <select name="institution_id" class="form-select" required>
                                    <option value="">Select...</option>
                                    <?php foreach ($institutions as $i): ?>
                                    <option value="<?= $i['id'] ?>"><?= e($i['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Flag Name</label>
                                <select name="flag_name" class="form-select" required>
                                    <?php foreach ($globalFlags as $f): ?>
                                    <option value="<?= e($f['name']) ?>"><?= e($f['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">State</label>
                                <select name="enabled" class="form-select">
                                    <option value="1">Enabled</option>
                                    <option value="0">Disabled</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Set Override</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
