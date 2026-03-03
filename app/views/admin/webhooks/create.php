<?php require APP_PATH . '/views/partials/header.php'; ?>

<div class="container py-4" style="max-width:700px">
    <h4 class="fw-bold mb-4">Create Webhook</h4>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form method="POST" action="<?= url('webhooks/store') ?>">
                <?= csrfField() ?>

                <div class="mb-3">
                    <label class="form-label">Endpoint URL <span class="text-danger">*</span></label>
                    <input type="url" name="url" class="form-control" placeholder="https://example.com/webhooks/certme" required>
                    <div class="form-text">We'll POST JSON payloads with HMAC-SHA256 signature to this URL.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Events to Subscribe</label>
                    <?php foreach ($events as $ev): ?>
                    <div class="form-check">
                        <input type="checkbox" name="events[]" value="<?= e($ev) ?>" class="form-check-input" id="ev_<?= e($ev) ?>" checked>
                        <label class="form-check-label" for="ev_<?= e($ev) ?>"><?= e($ev) ?></label>
                    </div>
                    <?php endforeach; ?>
                </div>

                <button type="submit" class="btn btn-primary">
                    <span class="material-symbols-rounded btn-icon">webhook</span> Create Webhook
                </button>
                <a href="<?= url('webhooks') ?>" class="btn btn-outline-secondary ms-2">Cancel</a>
            </form>
        </div>
    </div>
</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
