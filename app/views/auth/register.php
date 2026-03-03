<?php
$title = 'Create Account';
require APP_PATH . '/views/partials/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="auth-card">
                <div class="text-center mb-4">
                    <span class="material-symbols-rounded" style="font-size:48px;color:var(--md-primary)">person_add</span>
                    <h3 class="fw-bold mt-2">Get Started</h3>
                    <p class="text-muted">Create your CertiMe account</p>
                </div>

                <form method="POST" action="<?= url('register') ?>" autocomplete="on">
                    <?= csrfField() ?>

                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="full_name" name="full_name"
                               placeholder="Full Name" required
                               value="<?= e($_POST['full_name'] ?? '') ?>">
                        <label for="full_name">
                            <span class="material-symbols-rounded label-icon">badge</span>
                            Full Name
                        </label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" id="email" name="email"
                               placeholder="Email" required
                               value="<?= e($_POST['email'] ?? '') ?>">
                        <label for="email">
                            <span class="material-symbols-rounded label-icon">email</span>
                            Email Address
                        </label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="username" name="username"
                               placeholder="Username" required pattern="[a-zA-Z0-9_]{3,30}"
                               value="<?= e($_POST['username'] ?? '') ?>">
                        <label for="username">
                            <span class="material-symbols-rounded label-icon">alternate_email</span>
                            Username
                        </label>
                        <div class="form-text">3-30 characters, letters, numbers, underscores</div>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" id="password" name="password"
                               placeholder="Password" required minlength="8">
                        <label for="password">
                            <span class="material-symbols-rounded label-icon">lock</span>
                            Password
                        </label>
                        <div class="form-text">Minimum 8 characters</div>
                    </div>

                    <div class="form-floating mb-4">
                        <input type="password" class="form-control" id="password_confirm" name="password_confirm"
                               placeholder="Confirm Password" required minlength="8">
                        <label for="password_confirm">
                            <span class="material-symbols-rounded label-icon">lock_reset</span>
                            Confirm Password
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 btn-lg rounded-pill">
                        <span class="material-symbols-rounded btn-icon">how_to_reg</span>
                        Create Account
                    </button>
                </form>

                <div class="text-center mt-4">
                    <span class="text-muted">Already have an account?</span>
                    <a href="<?= url('login') ?>" class="fw-semibold text-decoration-none ms-1">Sign in</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
