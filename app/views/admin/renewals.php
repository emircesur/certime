<?php require APP_PATH . '/views/partials/header.php'; ?>

<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fw-bold">
                    <span class="material-symbols-rounded" style="font-size:36px;vertical-align:-6px;color:var(--md-primary)">schedule</span>
                    Expirations & Renewals
                </h1>
                <p class="text-muted">Manage credential expirations and automated renewals</p>
            </div>
            <a href="<?= url('admin') ?>" class="btn btn-outline-secondary rounded-pill">
                <span class="material-symbols-rounded" style="font-size:18px;vertical-align:-3px">arrow_back</span>
                Back to Admin
            </a>
        </div>

        <!-- Expiring Soon -->
        <div class="card shadow-sm border-0 mb-4" style="border-radius:16px">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="fw-semibold mb-0">
                    <span class="material-symbols-rounded text-warning" style="font-size:20px;vertical-align:-4px">warning</span>
                    Expiring Within 30 Days (<?= count($expiring) ?>)
                </h5>
            </div>
            <div class="card-body p-4">
                <?php if (empty($expiring)): ?>
                <p class="text-muted text-center py-3">No credentials expiring within the next 30 days.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Credential</th>
                                <th>Recipient</th>
                                <th>Expires</th>
                                <th>Days Left</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($expiring as $c):
                            $daysLeft = (int)((strtotime($c['expiration_date']) - time()) / 86400);
                        ?>
                            <tr>
                                <td>
                                    <strong><?= e($c['course_name']) ?></strong><br>
                                    <small class="text-muted"><?= e($c['credential_uid']) ?></small>
                                </td>
                                <td><?= e($c['full_name'] ?: $c['recipient_name'] ?? 'Unknown') ?></td>
                                <td><?= date('M j, Y', strtotime($c['expiration_date'])) ?></td>
                                <td>
                                    <span class="badge bg-<?= $daysLeft <= 7 ? 'danger' : ($daysLeft <= 14 ? 'warning' : 'info') ?>">
                                        <?= $daysLeft ?> days
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" action="<?= url('admin/credentials/' . $c['credential_uid'] . '/renew') ?>" class="d-inline">
                                        <?= csrfField() ?>
                                        <select name="months" class="form-select form-select-sm d-inline-block" style="width:auto">
                                            <option value="6">6 months</option>
                                            <option value="12" selected>12 months</option>
                                            <option value="24">24 months</option>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-success rounded-pill ms-1">
                                            <span class="material-symbols-rounded" style="font-size:16px">autorenew</span>
                                            Renew
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Already Expired -->
        <div class="card shadow-sm border-0" style="border-radius:16px">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="fw-semibold mb-0">
                    <span class="material-symbols-rounded text-danger" style="font-size:20px;vertical-align:-4px">error</span>
                    Expired Credentials (<?= count($expired) ?>)
                </h5>
            </div>
            <div class="card-body p-4">
                <?php if (empty($expired)): ?>
                <p class="text-muted text-center py-3">No expired credentials.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Credential</th>
                                <th>Recipient</th>
                                <th>Expired On</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($expired as $c): ?>
                            <tr>
                                <td>
                                    <strong><?= e($c['course_name']) ?></strong><br>
                                    <small class="text-muted"><?= e($c['credential_uid']) ?></small>
                                </td>
                                <td><?= e($c['full_name'] ?: $c['recipient_name'] ?? 'Unknown') ?></td>
                                <td class="text-danger"><?= date('M j, Y', strtotime($c['expiration_date'])) ?></td>
                                <td>
                                    <form method="POST" action="<?= url('admin/credentials/' . $c['credential_uid'] . '/renew') ?>" class="d-inline">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="months" value="12">
                                        <button type="submit" class="btn btn-sm btn-outline-success rounded-pill">
                                            <span class="material-symbols-rounded" style="font-size:16px">autorenew</span>
                                            Renew 12mo
                                        </button>
                                    </form>
                                    <form method="POST" action="<?= url('admin/credentials/' . $c['credential_uid'] . '/revoke') ?>" class="d-inline ms-1" onsubmit="return confirm('Revoke this expired credential?')">
                                        <?= csrfField() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">
                                            <span class="material-symbols-rounded" style="font-size:16px">block</span>
                                            Revoke
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
