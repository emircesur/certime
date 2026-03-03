<?php
$title = 'Endorsement Management';
require APP_PATH . '/views/partials/header.php';

$statusColors = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'];
$statusIcons = ['pending' => 'schedule', 'approved' => 'check_circle', 'rejected' => 'cancel'];
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-2">
            <?php require APP_PATH . '/views/admin/partials/nav.php'; ?>
        </div>
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1">Endorsements</h4>
                    <p class="text-muted mb-0">
                        Review and manage credential endorsements
                        <?php if ($pendingCount > 0): ?>
                            — <span class="badge bg-warning text-dark"><?= $pendingCount ?> pending</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <!-- Filter Tabs -->
            <ul class="nav nav-pills mb-4 gap-2">
                <?php foreach (['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'all' => 'All'] as $key => $label): ?>
                <li class="nav-item">
                    <a class="nav-link rounded-pill <?= $filter === $key ? 'active' : '' ?>" 
                       href="<?= url('admin/endorsements?filter=' . $key) ?>">
                        <?= $label ?>
                        <?php if ($key === 'pending' && $pendingCount > 0): ?>
                            <span class="badge bg-light text-dark ms-1"><?= $pendingCount ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>

            <?php if (empty($endorsements)): ?>
                <div class="material-card text-center py-5">
                    <span class="material-symbols-rounded mb-3" style="font-size:48px;color:var(--md-outline)">thumb_up_off_alt</span>
                    <h6 class="text-muted">No <?= $filter !== 'all' ? $filter : '' ?> endorsements found</h6>
                </div>
            <?php else: ?>
                <div class="material-card p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Endorser</th>
                                    <th>Credential</th>
                                    <th>Recipient</th>
                                    <th>Comment</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($endorsements as $e): ?>
                                <tr id="endorsement-<?= $e['id'] ?>">
                                    <td>
                                        <div class="fw-semibold"><?= e($e['endorser_name'] ?? '') ?></div>
                                        <?php if (!empty($e['endorser_org'])): ?>
                                        <div class="text-muted small"><?= e($e['endorser_org']) ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($e['endorser_email'])): ?>
                                        <div class="text-muted small"><?= e($e['endorser_email']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?= url('credential/' . e($e['credential_uid'])) ?>" class="text-decoration-none">
                                            <?= e($e['course_name'] ?? '') ?>
                                        </a>
                                    </td>
                                    <td><?= e($e['recipient_username'] ?? '') ?></td>
                                    <td>
                                        <div class="text-truncate" style="max-width:200px" title="<?= e($e['comment'] ?? '') ?>">
                                            <?= e($e['comment'] ?? '') ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $statusColors[$e['status'] ?? 'pending'] ?>">
                                            <span class="material-symbols-rounded align-middle" style="font-size:14px">
                                                <?= $statusIcons[$e['status'] ?? 'pending'] ?>
                                            </span>
                                            <?= ucfirst($e['status'] ?? 'pending') ?>
                                        </span>
                                    </td>
                                    <td class="text-muted small"><?= date('M j, Y', strtotime($e['created_at'] ?? 'now')) ?></td>
                                    <td>
                                        <?php if (($e['status'] ?? '') === 'pending'): ?>
                                        <div class="d-flex gap-1">
                                            <button class="btn btn-sm btn-success rounded-pill px-3" 
                                                    onclick="endorsementAction(<?= $e['id'] ?>, 'approve')">
                                                <span class="material-symbols-rounded" style="font-size:16px">check</span>
                                                Approve
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger rounded-pill px-3" 
                                                    onclick="endorsementAction(<?= $e['id'] ?>, 'reject')">
                                                <span class="material-symbols-rounded" style="font-size:16px">close</span>
                                                Reject
                                            </button>
                                        </div>
                                        <?php elseif (($e['status'] ?? '') === 'approved' && !empty($e['signature'])): ?>
                                            <span class="badge bg-primary-subtle text-primary">
                                                <span class="material-symbols-rounded" style="font-size:14px">verified</span>
                                                Signed
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted small">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function endorsementAction(id, action) {
    const url = action === 'approve' 
        ? '<?= url('endorsement/') ?>' + id + '/approve'
        : '<?= url('endorsement/') ?>' + id + '/reject';
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: '_csrf_token=' + encodeURIComponent(document.querySelector('meta[name="csrf-token"]').content)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const row = document.getElementById('endorsement-' + id);
            if (action === 'approve') {
                row.querySelector('td:nth-child(5)').innerHTML = '<span class="badge bg-success"><span class="material-symbols-rounded align-middle" style="font-size:14px">check_circle</span> Approved</span>';
            } else {
                row.querySelector('td:nth-child(5)').innerHTML = '<span class="badge bg-danger"><span class="material-symbols-rounded align-middle" style="font-size:14px">cancel</span> Rejected</span>';
            }
            row.querySelector('td:last-child').innerHTML = '<span class="text-muted small">Done</span>';
        } else {
            alert(data.error || 'Action failed');
        }
    })
    .catch(() => alert('Network error'));
}
</script>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
