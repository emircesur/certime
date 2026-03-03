<?php
$title = 'Manage Users';
require APP_PATH . '/views/partials/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-2">
            <?php require APP_PATH . '/views/admin/partials/nav.php'; ?>
        </div>
        <div class="col-lg-10">
            <div class="mb-4">
                <h4 class="fw-bold mb-1">Users</h4>
                <p class="text-muted mb-0"><?= $total ?? 0 ?> registered users</p>
            </div>

            <!-- Search -->
            <div class="material-card mb-4">
                <form method="GET" action="<?= url('admin/users') ?>" class="d-flex gap-2">
                    <div class="flex-grow-1">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0">
                                <span class="material-symbols-rounded text-muted">search</span>
                            </span>
                            <input type="text" class="form-control border-start-0" name="q"
                                   placeholder="Search by name, username, email..." value="<?= e($search ?? '') ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Search</button>
                </form>
            </div>

            <!-- Users Table -->
            <div class="material-card">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Credentials</th>
                                <th>Joined</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                            <tr><td colspan="7" class="text-center text-muted py-5">No users found</td></tr>
                            <?php else: foreach ($users as $u): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="user-avatar">
                                            <?= strtoupper(substr($u['username'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-medium"><?= e($u['full_name'] ?: $u['username']) ?></div>
                                            <div class="text-muted small">@<?= e($u['username']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-muted small"><?= e($u['email']) ?></td>
                                <td>
                                    <?php
                                    $roleColors = ['admin' => 'danger', 'moderator' => 'warning', 'student' => 'primary'];
                                    $roleColor = $roleColors[$u['role']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $roleColor ?>-subtle text-<?= $roleColor ?> rounded-pill">
                                        <?= e(ucfirst($u['role'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($u['is_active'] ?? 1): ?>
                                        <span class="badge bg-success-subtle text-success rounded-pill">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger rounded-pill">Disabled</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted"><?= (int)($u['credential_count'] ?? 0) ?></td>
                                <td class="text-muted small"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                                <td class="text-end">
                                    <div class="d-flex gap-1 justify-content-end">
                                        <?php if (isAdmin() && $u['id'] != ($_SESSION['user_id'] ?? 0)): ?>
                                        <!-- Role Change -->
                                        <form method="POST" action="<?= url('admin/users/' . (int)$u['id'] . '/role') ?>" class="d-inline">
                                            <?= csrfField() ?>
                                            <select name="role" class="form-select form-select-sm rounded-pill" 
                                                    onchange="this.form.submit()" style="width:auto;font-size:0.8rem">
                                                <option value="student" <?= $u['role'] === 'student' ? 'selected' : '' ?>>Student</option>
                                                <option value="moderator" <?= $u['role'] === 'moderator' ? 'selected' : '' ?>>Moderator</option>
                                                <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                            </select>
                                        </form>
                                        <!-- Toggle Active -->
                                        <form method="POST" action="<?= url('admin/users/' . (int)$u['id'] . '/toggle') ?>" class="d-inline">
                                            <?= csrfField() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-<?= ($u['is_active'] ?? 1) ? 'warning' : 'success' ?> rounded-pill"
                                                    title="<?= ($u['is_active'] ?? 1) ? 'Disable' : 'Enable' ?>">
                                                <span class="material-symbols-rounded" style="font-size:18px">
                                                    <?= ($u['is_active'] ?? 1) ? 'person_off' : 'person' ?>
                                                </span>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (($totalPages ?? 1) > 1): ?>
                <div class="d-flex justify-content-center py-3">
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= ($page ?? 1) == $i ? 'active' : '' ?>">
                                <a class="page-link" href="<?= url('admin/users?page=' . $i . (!empty($search) ? '&q=' . urlencode($search) : '')) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
