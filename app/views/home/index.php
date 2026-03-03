<?php
$title = 'Home';
require APP_PATH . '/views/partials/header.php';

$db = \Database::getInstance();
$totalCreds = $db->query("SELECT COUNT(*) as c FROM credentials")->fetch()['c'] ?? 0;
$totalUsers = $db->query("SELECT COUNT(*) as c FROM users")->fetch()['c'] ?? 0;
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center min-vh-75">
            <div class="col-lg-6">
                <div class="hero-badge mb-3">
                    <span class="material-symbols-rounded" style="font-size:16px">bolt</span>
                    Open Badges 3.0 Compliant
                </div>
                <h1 class="display-4 fw-bold mb-3">
                    Digital Credentials<br>
                    <span class="text-gradient">You Can Trust</span>
                </h1>
                <p class="lead text-muted mb-4">
                    Issue, verify, and share tamper-proof digital credentials backed by 
                    Ed25519 cryptographic signatures, Merkle tree integrity proofs, and 
                    W3C Verifiable Credentials standards.
                </p>
                <div class="d-flex gap-3 flex-wrap">
                    <?php if (isLoggedIn()): ?>
                        <a href="<?= url('portfolio') ?>" class="btn btn-primary btn-lg rounded-pill px-4">
                            <span class="material-symbols-rounded btn-icon">folder_shared</span>
                            My Portfolio
                        </a>
                    <?php else: ?>
                        <a href="<?= url('register') ?>" class="btn btn-primary btn-lg rounded-pill px-4">
                            <span class="material-symbols-rounded btn-icon">rocket_launch</span>
                            Get Started Free
                        </a>
                        <a href="<?= url('login') ?>" class="btn btn-outline-primary btn-lg rounded-pill px-4">
                            Sign In
                        </a>
                    <?php endif; ?>
                    <a href="<?= url('verify') ?>" class="btn btn-outline-secondary btn-lg rounded-pill px-4">
                        <span class="material-symbols-rounded btn-icon">verified</span>
                        Verify Credential
                    </a>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block">
                <div class="hero-visual">
                    <div class="hero-card card-1">
                        <span class="material-symbols-rounded" style="font-size:48px;color:var(--md-primary)">workspace_premium</span>
                        <div class="mt-2 fw-semibold">Digital Certificate</div>
                        <div class="small text-muted">Cryptographically signed</div>
                    </div>
                    <div class="hero-card card-2">
                        <span class="material-symbols-rounded" style="font-size:36px;color:var(--md-tertiary)">shield_with_heart</span>
                        <div class="mt-2 fw-medium small">Ed25519 Verified</div>
                    </div>
                    <div class="hero-card card-3">
                        <span class="material-symbols-rounded" style="font-size:36px;color:var(--md-secondary)">account_tree</span>
                        <div class="mt-2 fw-medium small">Merkle Proof</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Bar -->
<section class="py-4 bg-body-tertiary">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-md-4">
                <div class="stat-number"><?= number_format($totalCreds) ?></div>
                <div class="text-muted small">Credentials Issued</div>
            </div>
            <div class="col-md-4">
                <div class="stat-number"><?= number_format($totalUsers) ?></div>
                <div class="text-muted small">Registered Users</div>
            </div>
            <div class="col-md-4">
                <div class="stat-number">100%</div>
                <div class="text-muted small">Cryptographically Verified</div>
            </div>
        </div>
    </div>
</section>

<!-- Features -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Built on Open Standards</h2>
            <p class="text-muted">Enterprise-grade credentialing with zero vendor lock-in</p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon bg-primary-subtle">
                        <span class="material-symbols-rounded" style="color:var(--md-primary)">verified</span>
                    </div>
                    <h5 class="fw-semibold">Open Badges 3.0</h5>
                    <p class="text-muted small mb-0">
                        W3C Verifiable Credentials compliant badges with JSON-LD context, 
                        fully interoperable with the global credential ecosystem.
                    </p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon bg-secondary-subtle">
                        <span class="material-symbols-rounded" style="color:var(--md-secondary)">key</span>
                    </div>
                    <h5 class="fw-semibold">Ed25519 Signatures</h5>
                    <p class="text-muted small mb-0">
                        Every credential is digitally signed with Ed25519 elliptic curve 
                        cryptography, ensuring tamper-proof authenticity.
                    </p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon bg-tertiary-subtle">
                        <span class="material-symbols-rounded" style="color:var(--md-tertiary)">account_tree</span>
                    </div>
                    <h5 class="fw-semibold">Merkle Tree Proofs</h5>
                    <p class="text-muted small mb-0">
                        Batch credential integrity via cryptographic Merkle trees with 
                        individually verifiable inclusion proofs.
                    </p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon" style="background:rgba(var(--md-error-rgb),.1)">
                        <span class="material-symbols-rounded" style="color:var(--md-error)">picture_as_pdf</span>
                    </div>
                    <h5 class="fw-semibold">Signed PDF Certificates</h5>
                    <p class="text-muted small mb-0">
                        Beautiful PDF certificates with embedded X.509 digital signatures, 
                        QR codes, and institutional branding.
                    </p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon" style="background:rgba(0,119,181,.1)">
                        <span class="material-symbols-rounded" style="color:#0077b5">share</span>
                    </div>
                    <h5 class="fw-semibold">LinkedIn Integration</h5>
                    <p class="text-muted small mb-0">
                        One-click sharing to LinkedIn profiles with proper credential 
                        metadata for professional visibility.
                    </p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon" style="background:rgba(139,92,246,.1)">
                        <span class="material-symbols-rounded" style="color:#8b5cf6">smart_toy</span>
                    </div>
                    <h5 class="fw-semibold">AI-Powered Advisor</h5>
                    <p class="text-muted small mb-0">
                        Gemini-powered AI assistant provides career guidance, credential 
                        recommendations, and portfolio insights.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="py-5 bg-body-tertiary">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">How It Works</h2>
            <p class="text-muted">Three simple steps to verifiable digital credentials</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="text-center">
                    <div class="step-number">1</div>
                    <h5 class="fw-semibold mt-3">Issue</h5>
                    <p class="text-muted small">
                        Administrators create credentials with achievement details, 
                        which are automatically signed with Ed25519 keys.
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center">
                    <div class="step-number">2</div>
                    <h5 class="fw-semibold mt-3">Verify</h5>
                    <p class="text-muted small">
                        Anyone can verify a credential's authenticity using the 
                        cryptographic signature and Merkle tree proof.
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center">
                    <div class="step-number">3</div>
                    <h5 class="fw-semibold mt-3">Share</h5>
                    <p class="text-muted small">
                        Recipients share credentials on LinkedIn, download signed PDFs, 
                        or export their full portfolio as JSON.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-5">
    <div class="container">
        <div class="cta-card text-center">
            <h3 class="fw-bold mb-3">Ready to Get Started?</h3>
            <p class="text-muted mb-4">Join the open credentials revolution today.</p>
            <?php if (!isLoggedIn()): ?>
                <a href="<?= url('register') ?>" class="btn btn-primary btn-lg rounded-pill px-5">
                    Create Your Account
                </a>
            <?php else: ?>
                <a href="<?= url('portfolio') ?>" class="btn btn-primary btn-lg rounded-pill px-5">
                    View My Portfolio
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
