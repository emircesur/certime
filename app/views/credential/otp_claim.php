<?php require APP_PATH . '/views/partials/header.php'; ?>

<div class="container py-5" style="max-width:500px">
    <div class="text-center mb-4">
        <span class="material-symbols-rounded display-3 text-primary">workspace_premium</span>
        <h3 class="fw-bold mt-2">Claim Your Badge</h3>
        <p class="text-muted">Enter your email and the one-time code you received to claim your digital credential.</p>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form method="POST" action="<?= url('claim/verify') ?>">
                <?= csrfField() ?>

                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control form-control-lg" placeholder="your@email.com" required autofocus>
                </div>

                <div class="mb-3">
                    <label class="form-label">Verification Code</label>
                    <input type="text" name="code" class="form-control form-control-lg text-center tracking-widest" placeholder="000000" maxlength="6" required
                           style="font-size:2rem;letter-spacing:0.5em;font-weight:bold">
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill">
                    <span class="material-symbols-rounded btn-icon">verified</span> Claim Badge
                </button>
            </form>
        </div>
    </div>
</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
