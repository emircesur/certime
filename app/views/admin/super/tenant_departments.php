<?php require APP_PATH . '/views/partials/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-2"><?php require APP_PATH . '/views/admin/partials/nav.php'; ?></div>
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1">Departments — <?= e($institution['name']) ?></h4>
                    <p class="text-muted mb-0"><?= count($departments) ?> department(s), <?= count($members) ?> member(s)</p>
                </div>
                <a href="<?= url('admin/tenants') ?>" class="btn btn-outline-secondary rounded-pill">
                    <span class="material-symbols-rounded btn-icon">arrow_back</span> Back
                </a>
            </div>

            <div class="row g-4">
                <!-- Departments -->
                <div class="col-lg-7">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Departments</h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light"><tr><th>Name</th><th>Description</th><th>Members</th><th>Credentials</th></tr></thead>
                                <tbody>
                                <?php if (empty($departments)): ?>
                                    <tr><td colspan="4" class="text-center text-muted py-3">No departments yet.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($departments as $d): ?>
                                    <tr>
                                        <td><strong><?= e($d['name']) ?></strong></td>
                                        <td class="text-muted"><?= e($d['description'] ?? '') ?></td>
                                        <td><?= $d['member_count'] ?? 0 ?></td>
                                        <td><?= $d['credential_count'] ?? 0 ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Create Department -->
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="mb-3">Create Department</h6>
                            <form method="POST" action="<?= url('admin/tenants/' . $institution['id'] . '/departments/create') ?>">
                                <?= csrfField() ?>
                                <div class="row g-2">
                                    <div class="col-md-5">
                                        <input type="text" name="name" class="form-control" placeholder="Department name" required>
                                    </div>
                                    <div class="col-md-5">
                                        <input type="text" name="description" class="form-control" placeholder="Description (optional)">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary w-100">Add</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Members -->
                <div class="col-lg-5">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-transparent"><h6 class="mb-0">Members</h6></div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light"><tr><th>User</th><th>Role</th></tr></thead>
                                <tbody>
                                <?php foreach ($members as $m): ?>
                                <tr>
                                    <td><?= e($m['username'] ?? $m['email'] ?? 'User #' . $m['user_id']) ?></td>
                                    <td><span class="badge bg-info"><?= e($m['role']) ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($members)): ?>
                                    <tr><td colspan="2" class="text-center text-muted py-3">No members.</td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Add Member -->
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="mb-3">Add / Invite Member</h6>
                            <form method="POST" action="<?= url('admin/tenants/' . $institution['id'] . '/members/add') ?>">
                                <?= csrfField() ?>
                                <div class="mb-2">
                                    <input type="email" name="email" class="form-control" placeholder="user@example.com" required>
                                </div>
                                <div class="mb-2">
                                    <select name="role" class="form-select">
                                        <option value="viewer">Viewer</option>
                                        <option value="designer">Badge Designer</option>
                                        <option value="issuer">Issuer</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <select name="department_id" class="form-select">
                                        <option value="">— No department —</option>
                                        <?php foreach ($departments as $d): ?>
                                        <option value="<?= $d['id'] ?>"><?= e($d['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Add Member</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
