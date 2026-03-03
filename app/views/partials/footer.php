
<!-- Footer -->
<footer class="site-footer mt-auto">
    <div class="container">
        <div class="row g-4 py-4">
            <div class="col-lg-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="material-symbols-rounded" style="font-size:28px;color:var(--md-primary)">verified</span>
                    <span class="fw-bold fs-5">CertiMe</span>
                </div>
                <p class="text-muted small mb-0">
                    Open-source digital credentialing platform built on Open Badges 3.0 
                    and W3C Verifiable Credentials standards.
                </p>
            </div>
            <div class="col-lg-2 col-md-4">
                <h6 class="fw-semibold mb-3">Platform</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="<?= url('') ?>" class="text-muted text-decoration-none">Home</a></li>
                    <li class="mb-2"><a href="<?= url('verify') ?>" class="text-muted text-decoration-none">Verify</a></li>
                    <li class="mb-2"><a href="<?= url('login') ?>" class="text-muted text-decoration-none">Sign In</a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-4">
                <h6 class="fw-semibold mb-3">Standards</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="https://www.imsglobal.org/spec/ob/v3p0/" target="_blank" class="text-muted text-decoration-none">Open Badges 3.0</a></li>
                    <li class="mb-2"><a href="https://www.w3.org/TR/vc-data-model/" target="_blank" class="text-muted text-decoration-none">W3C Verifiable Credentials</a></li>
                    <li class="mb-2"><a href="https://w3c-ccg.github.io/lds-ed25519-2020/" target="_blank" class="text-muted text-decoration-none">Ed25519Signature2020</a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-4">
                <h6 class="fw-semibold mb-3">Technology</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2 text-muted">Ed25519 Digital Signatures</li>
                    <li class="mb-2 text-muted">Merkle Tree Integrity</li>
                    <li class="mb-2 text-muted">X.509 PDF Signing</li>
                </ul>
            </div>
        </div>
        <hr class="my-0">
        <div class="d-flex flex-wrap justify-content-between align-items-center py-3">
            <small class="text-muted">&copy; <?= date('Y') ?> CertiMe. All rights reserved.</small>
            <small class="text-muted">Built with cryptographic trust</small>
        </div>
    </div>
</footer>

<!-- Bootstrap 5.3 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- App JS -->
<script src="<?= asset('js/main.js') ?>"></script>

<?php if (!empty($extraJs)): ?>
<?= $extraJs ?>
<?php endif; ?>

</body>
</html>
