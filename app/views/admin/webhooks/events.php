<?php require APP_PATH . '/views/partials/header.php'; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Webhook #<?= $webhookId ?> — Event Log</h4>
        <a href="<?= url('webhooks') ?>" class="btn btn-outline-secondary rounded-pill">
            <span class="material-symbols-rounded btn-icon">arrow_back</span> Back
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-light">
                    <tr><th>Event</th><th>Status</th><th>Response</th><th>Sent At</th></tr>
                </thead>
                <tbody>
                <?php if (empty($events)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">No events delivered yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($events as $ev): ?>
                    <tr>
                        <td><code><?= e($ev['event_type']) ?></code></td>
                        <td>
                            <span class="badge bg-<?= ($ev['response_code'] ?? 0) >= 200 && ($ev['response_code'] ?? 0) < 300 ? 'success' : 'danger' ?>">
                                HTTP <?= e($ev['response_code'] ?? '???') ?>
                            </span>
                        </td>
                        <td class="small text-muted" style="max-width:300px;overflow:hidden;text-overflow:ellipsis">
                            <?= e(substr($ev['response_body'] ?? '', 0, 200)) ?>
                        </td>
                        <td><?= e($ev['sent_at']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
