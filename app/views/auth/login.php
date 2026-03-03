<?php
$title = 'Sign In';
require APP_PATH . '/views/partials/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="auth-card">
                <div class="text-center mb-4">
                    <span class="material-symbols-rounded" style="font-size:48px;color:var(--md-primary)">lock_open</span>
                    <h3 class="fw-bold mt-2">Welcome Back</h3>
                    <p class="text-muted">Sign in to your CertiMe account</p>
                </div>

                <form method="POST" action="<?= url('login') ?>" autocomplete="on">
                    <?= csrfField() ?>

                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="username" name="username" 
                               placeholder="Email or Username" required autofocus
                               value="<?= e($_POST['username'] ?? '') ?>">
                        <label for="username">
                            <span class="material-symbols-rounded label-icon">person</span>
                            Email or Username
                        </label>
                    </div>

                    <div class="form-floating mb-4">
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Password" required>
                        <label for="password">
                            <span class="material-symbols-rounded label-icon">lock</span>
                            Password
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 btn-lg rounded-pill">
                        <span class="material-symbols-rounded btn-icon">login</span>
                        Sign In
                    </button>
                </form>

                <div class="text-center mt-4">
                    <span class="text-muted">Don't have an account?</span>
                    <a href="<?= url('register') ?>" class="fw-semibold text-decoration-none ms-1">Create one</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
