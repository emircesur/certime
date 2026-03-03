<?php require APP_PATH . '/views/partials/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-2">
            <?php require APP_PATH . '/views/admin/partials/nav.php'; ?>
        </div>
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1">Tenant Management Console</h4>
                    <p class="text-muted mb-0"><?= $totalTenants ?? 0 ?> institution(s) registered</p>
                </div>
                <a href="<?= url('admin/tenants/create') ?>" class="btn btn-primary rounded-pill">
                    <span class="material-symbols-rounded btn-icon">add</span> Onboard Institution
                </a>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($institutions)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">No institutions yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($institutions as $inst): ?>
                            <tr>
                                <td>#<?= $inst['id'] ?></td>
                                <td><strong><?= e($inst['name']) ?></strong></td>
                                <td><code><?= e($inst['slug']) ?></code></td>
                                <td>
                                    <span class="badge bg-<?= match($inst['status']) { 'active' => 'success', 'suspended' => 'warning', 'terminated' => 'danger', default => 'secondary' } ?>">
                                        <?= e(ucfirst($inst['status'])) ?>
                                    </span>
                                </td>
                                <td><?= e($inst['created_at']) ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= url('admin/tenants/' . $inst['id'] . '/departments') ?>" class="btn btn-outline-primary" title="Departments">
                                            <span class="material-symbols-rounded" style="font-size:16px">apartment</span>
                                        </a>
                                        <?php if ($inst['status'] === 'active'): ?>
                                        <form method="POST" action="<?= url('admin/tenants/' . $inst['id'] . '/action') ?>" class="d-inline">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="action" value="suspend">
                                            <button class="btn btn-outline-warning" title="Suspend"><span class="material-symbols-rounded" style="font-size:16px">pause</span></button>
                                        </form>
                                        <?php elseif ($inst['status'] === 'suspended'): ?>
                                        <form method="POST" action="<?= url('admin/tenants/' . $inst['id'] . '/action') ?>" class="d-inline">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="action" value="activate">
                                            <button class="btn btn-outline-success" title="Activate"><span class="material-symbols-rounded" style="font-size:16px">play_arrow</span></button>
                                        </form>
                                        <form method="POST" action="<?= url('admin/tenants/' . $inst['id'] . '/action') ?>" class="d-inline">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="action" value="terminate">
                                            <button class="btn btn-outline-danger" title="Terminate" onclick="return confirm('Permanently terminate this institution?')">
                                                <span class="material-symbols-rounded" style="font-size:16px">delete_forever</span>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
