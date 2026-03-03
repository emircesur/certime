<?php require APP_PATH . '/views/partials/header.php'; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Webhooks</h4>
            <p class="text-muted mb-0">Receive real-time event notifications via HTTP POST</p>
        </div>
        <a href="<?= url('webhooks/create') ?>" class="btn btn-primary rounded-pill">
            <span class="material-symbols-rounded btn-icon">add</span> New Webhook
        </a>
    </div>

    <?php if (empty($webhooks)): ?>
    <div class="card shadow-sm border-0 text-center py-5">
        <div class="card-body">
            <span class="material-symbols-rounded display-3 text-muted">webhook</span>
            <h5 class="mt-3">No webhooks configured</h5>
            <p class="text-muted">Create a webhook to receive real-time notifications about credential events.</p>
            <a href="<?= url('webhooks/create') ?>" class="btn btn-primary">Create Webhook</a>
        </div>
    </div>
    <?php else: ?>
    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>URL</th><th>Events</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php foreach ($webhooks as $wh): ?>
                <tr>
                    <td>
                        <code class="small"><?= e($wh['url']) ?></code>
                        <div class="text-muted small">Secret: <?= e(substr($wh['secret'], 0, 8)) ?>...</div>
                    </td>
                    <td>
                        <?php foreach (json_decode($wh['events'] ?? '[]', true) as $ev): ?>
                        <span class="badge bg-light text-dark me-1"><?= e($ev) ?></span>
                        <?php endforeach; ?>
                    </td>
                    <td>
                        <span class="badge bg-<?= $wh['is_active'] ? 'success' : 'secondary' ?>"><?= $wh['is_active'] ? 'Active' : 'Paused' ?></span>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <form method="POST" action="<?= url('webhooks/' . $wh['id'] . '/test') ?>" class="d-inline"><?= csrfField() ?><button class="btn btn-outline-info" title="Test"><span class="material-symbols-rounded" style="font-size:16px">send</span></button></form>
                            <form method="POST" action="<?= url('webhooks/' . $wh['id'] . '/toggle') ?>" class="d-inline"><?= csrfField() ?><button class="btn btn-outline-warning" title="Toggle"><span class="material-symbols-rounded" style="font-size:16px"><?= $wh['is_active'] ? 'pause' : 'play_arrow' ?></span></button></form>
                            <a href="<?= url('webhooks/' . $wh['id'] . '/events') ?>" class="btn btn-outline-primary" title="Events"><span class="material-symbols-rounded" style="font-size:16px">history</span></a>
                            <form method="POST" action="<?= url('webhooks/' . $wh['id'] . '/delete') ?>" class="d-inline" onclick="return confirm('Delete this webhook?')"><?= csrfField() ?><button class="btn btn-outline-danger" title="Delete"><span class="material-symbols-rounded" style="font-size:16px">delete</span></button></form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
