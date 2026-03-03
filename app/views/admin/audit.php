<?php
$title = 'Audit Log';
require APP_PATH . '/views/partials/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-2">
            <?php require APP_PATH . '/views/admin/partials/nav.php'; ?>
        </div>
        <div class="col-lg-10">
            <div class="mb-4">
                <h4 class="fw-bold mb-1">Audit Log</h4>
                <p class="text-muted mb-0">System activity and security events</p>
            </div>

            <div class="material-card">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Details</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($logs)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-5">
                                <span class="material-symbols-rounded d-block mb-2" style="font-size:48px">history</span>
                                No audit entries yet
                            </td></tr>
                            <?php else: foreach ($logs as $log): ?>
                            <tr>
                                <td class="text-muted small text-nowrap">
                                    <?= date('M j, H:i:s', strtotime($log['timestamp'])) ?>
                                </td>
                                <td>
                                    <?php if ($log['username'] ?? null): ?>
                                        <span class="fw-medium"><?= e($log['username']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">System</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $actionIcons = [
                                        'login' => ['login', 'success'],
                                        'login_failed' => ['error', 'danger'],
                                        'logout' => ['logout', 'secondary'],
                                        'register' => ['person_add', 'primary'],
                                        'credential_created' => ['add_circle', 'success'],
                                        'credential_revoked' => ['block', 'danger'],
                                        'role_changed' => ['swap_horiz', 'warning'],
                                        'user_toggled' => ['toggle_on', 'warning'],
                                        'keys_generated' => ['key', 'primary'],
                                        'endorsement_created' => ['thumb_up', 'tertiary'],
                                        'endorsement_approved' => ['check', 'success'],
                                        'endorsement_rejected' => ['close', 'danger'],
                                    ];
                                    $icon = $actionIcons[$log['action']] ?? ['info', 'secondary'];
                                    ?>
                                    <span class="badge bg-<?= $icon[1] ?>-subtle text-<?= $icon[1] ?> rounded-pill">
                                        <span class="material-symbols-rounded" style="font-size:14px"><?= $icon[0] ?></span>
                                        <?= e(str_replace('_', ' ', ucfirst($log['action']))) ?>
                                    </span>
                                </td>
                                <td class="text-muted small"><?= e($log['details'] ?? '') ?></td>
                                <td class="text-muted small font-monospace"><?= e($log['ip_address'] ?? '') ?></td>
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
                                <a class="page-link" href="<?= url('admin/audit?page=' . $i) ?>"><?= $i ?></a>
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
