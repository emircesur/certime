<?php require APP_PATH . '/views/partials/header.php'; ?>

<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fw-bold">
                    <span class="material-symbols-rounded" style="font-size:36px;vertical-align:-6px;color:var(--md-primary)">group</span>
                    Team Management
                </h1>
                <p class="text-muted">Manage your team members and their access</p>
            </div>
        </div>

        <?php if (!$subscription || !in_array($subscription['plan_slug'] ?? '', ['team', 'institution'])): ?>
        <div class="card shadow-sm border-0 mb-4" style="border-radius:16px">
            <div class="card-body text-center py-5">
                <span class="material-symbols-rounded text-muted mb-3" style="font-size:64px">lock</span>
                <h4>Team Management requires a Team or Institution plan</h4>
                <p class="text-muted">Upgrade your plan to invite team members and manage credentials collaboratively.</p>
                <a href="<?= url('pricing') ?>" class="btn btn-primary rounded-pill px-4">
                    <span class="material-symbols-rounded" style="font-size:18px;vertical-align:-3px">upgrade</span>
                    View Plans
                </a>
            </div>
        </div>
        <?php else: ?>

        <!-- Add Member -->
        <div class="card shadow-sm border-0 mb-4" style="border-radius:16px">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="fw-semibold mb-0">
                    <span class="material-symbols-rounded" style="font-size:20px;vertical-align:-4px">person_add</span>
                    Add Team Member
                </h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="<?= url('team/add') ?>" class="row g-3">
                    <?= csrfField() ?>
                    <div class="col-md-5">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="member@example.com" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select">
                            <option value="member">Member</option>
                            <option value="issuer">Issuer</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary rounded-pill w-100">
                            <span class="material-symbols-rounded" style="font-size:18px;vertical-align:-3px">add</span>
                            Add Member
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Members List -->
        <div class="card shadow-sm border-0" style="border-radius:16px">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="fw-semibold mb-0">
                    <span class="material-symbols-rounded" style="font-size:20px;vertical-align:-4px">groups</span>
                    Team Members (<?= count($members) ?>)
                </h5>
            </div>
            <div class="card-body p-4">
                <?php if (empty($members)): ?>
                <p class="text-muted text-center py-4">No team members yet. Add your first member above.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Joined</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($members as $m): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="material-symbols-rounded text-muted">person</span>
                                        <strong><?= e($m['full_name'] ?: $m['username']) ?></strong>
                                    </div>
                                </td>
                                <td class="text-muted"><?= e($m['email']) ?></td>
                                <td><span class="badge bg-primary-subtle text-primary"><?= e(ucfirst($m['role'])) ?></span></td>
                                <td class="text-muted small"><?= date('M j, Y', strtotime($m['joined_at'])) ?></td>
                                <td>
                                    <form method="POST" action="<?= url('team/remove/' . $m['user_id']) ?>" class="d-inline" onsubmit="return confirm('Remove this member?')">
                                        <?= csrfField() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">
                                            <span class="material-symbols-rounded" style="font-size:16px">close</span>
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
        <?php endif; ?>

        <?php if (!empty($myTeams)): ?>
        <!-- Teams I belong to -->
        <div class="card shadow-sm border-0 mt-4" style="border-radius:16px">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="fw-semibold mb-0">
                    <span class="material-symbols-rounded" style="font-size:20px;vertical-align:-4px">badge</span>
                    Teams I Belong To
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead><tr><th>Organization</th><th>Owner</th><th>My Role</th></tr></thead>
                        <tbody>
                        <?php foreach ($myTeams as $t): ?>
                            <tr>
                                <td><strong><?= e($t['institution_name'] ?: 'Team') ?></strong></td>
                                <td class="text-muted"><?= e($t['owner_name']) ?></td>
                                <td><span class="badge bg-info-subtle text-info"><?= e(ucfirst($t['role'])) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
