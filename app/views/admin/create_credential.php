<?php
$title = 'Issue Credential';
require APP_PATH . '/views/partials/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-2">
            <?php require APP_PATH . '/views/admin/partials/nav.php'; ?>
        </div>
        <div class="col-lg-10">
            <div class="mb-4">
                <h4 class="fw-bold mb-1">Issue New Credential</h4>
                <p class="text-muted mb-0">Create and sign a new digital credential</p>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="material-card">
                        <form method="POST" action="<?= url('admin/create') ?>">
                            <?= csrfField() ?>

                            <h6 class="fw-semibold text-muted text-uppercase small mb-3">Recipient Information</h6>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <select class="form-select" id="user_id" name="user_id" required>
                                            <option value="">Select recipient...</option>
                                            <?php foreach ($users ?? [] as $u): ?>
                                                <option value="<?= (int)$u['id'] ?>"
                                                    <?= (($_POST['user_id'] ?? '') == $u['id']) ? 'selected' : '' ?>>
                                                    <?= e($u['full_name'] ?: $u['username']) ?> (<?= e($u['email']) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <label for="user_id">Recipient</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="recipient_name" name="recipient_name"
                                               placeholder="Name on certificate" required
                                               value="<?= e($_POST['recipient_name'] ?? '') ?>">
                                        <label for="recipient_name">Name on Certificate</label>
                                    </div>
                                </div>
                            </div>

                            <h6 class="fw-semibold text-muted text-uppercase small mb-3">Credential Details</h6>

                            <div class="row g-3 mb-3">
                                <div class="col-md-8">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="title" name="title"
                                               placeholder="Title" required
                                               value="<?= e($_POST['title'] ?? '') ?>">
                                        <label for="title">Credential Title</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <select class="form-select" id="category" name="category">
                                            <option value="General">General</option>
                                            <option value="Academic" <?= ($_POST['category'] ?? '') === 'Academic' ? 'selected' : '' ?>>Academic</option>
                                            <option value="Professional" <?= ($_POST['category'] ?? '') === 'Professional' ? 'selected' : '' ?>>Professional</option>
                                            <option value="Skill" <?= ($_POST['category'] ?? '') === 'Skill' ? 'selected' : '' ?>>Skill</option>
                                            <option value="Workshop" <?= ($_POST['category'] ?? '') === 'Workshop' ? 'selected' : '' ?>>Workshop</option>
                                            <option value="Competition" <?= ($_POST['category'] ?? '') === 'Competition' ? 'selected' : '' ?>>Competition</option>
                                        </select>
                                        <label for="category">Category</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <select class="form-select" id="credential_type" name="credential_type">
                                            <option value="certificate" <?= ($_POST['credential_type'] ?? '') === 'certificate' ? 'selected' : '' ?>>Certificate</option>
                                            <option value="badge" <?= ($_POST['credential_type'] ?? '') === 'badge' ? 'selected' : '' ?>>Digital Badge</option>
                                            <option value="degree" <?= ($_POST['credential_type'] ?? '') === 'degree' ? 'selected' : '' ?>>Degree</option>
                                            <option value="diploma" <?= ($_POST['credential_type'] ?? '') === 'diploma' ? 'selected' : '' ?>>Diploma</option>
                                            <option value="license" <?= ($_POST['credential_type'] ?? '') === 'license' ? 'selected' : '' ?>>License</option>
                                            <option value="micro-credential" <?= ($_POST['credential_type'] ?? '') === 'micro-credential' ? 'selected' : '' ?>>Micro-Credential</option>
                                            <option value="course" <?= ($_POST['credential_type'] ?? '') === 'course' ? 'selected' : '' ?>>Course Completion</option>
                                            <option value="workshop" <?= ($_POST['credential_type'] ?? '') === 'workshop' ? 'selected' : '' ?>>Workshop</option>
                                        </select>
                                        <label for="credential_type">Credential Type</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <input type="number" class="form-control" id="credit_hours" name="credit_hours"
                                               placeholder="Credit Hours" step="0.5" min="0" max="200"
                                               value="<?= e($_POST['credit_hours'] ?? '0') ?>">
                                        <label for="credit_hours">Credit Hours</label>
                                    </div>
                                    <div class="form-text">Academic credit value (0 = none)</div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="issuer" name="issuer"
                                               placeholder="Issuer" required
                                               value="<?= e($_POST['issuer'] ?? 'CertiMe Platform') ?>">
                                        <label for="issuer">Issuing Organization</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-floating mb-3">
                                <textarea class="form-control" id="description" name="description" 
                                          placeholder="Description" style="height:100px" required><?= e($_POST['description'] ?? '') ?></textarea>
                                <label for="description">Description</label>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-12">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="skills" name="skills"
                                               placeholder="Skills"
                                               value="<?= e($_POST['skills'] ?? '') ?>">
                                        <label for="skills">Skills (comma-separated)</label>
                                    </div>
                                    <div class="form-text">e.g. PHP, Cryptography, Web Development</div>
                                </div>
                            </div>

                            <div class="form-floating mb-4">
                                <textarea class="form-control" id="criteria" name="criteria"
                                          placeholder="Criteria" style="height:80px"><?= e($_POST['criteria'] ?? '') ?></textarea>
                                <label for="criteria">Achievement Criteria</label>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary rounded-pill px-4">
                                    <span class="material-symbols-rounded btn-icon">workspace_premium</span>
                                    Issue Credential
                                </button>
                                <a href="<?= url('admin/credentials') ?>" class="btn btn-outline-secondary rounded-pill px-4">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="material-card bg-body-tertiary">
                        <h6 class="fw-semibold mb-3">
                            <span class="material-symbols-rounded align-middle me-1" style="font-size:20px">info</span>
                            About Credential Issuance
                        </h6>
                        <ul class="small text-muted mb-0" style="padding-left:1.2rem">
                            <li class="mb-2">Each credential gets a unique cryptographic ID</li>
                            <li class="mb-2">Digitally signed with Ed25519 upon creation</li>
                            <li class="mb-2">Open Badges 3.0 / W3C VC compliant format</li>
                            <li class="mb-2">Automatically added to recipient's portfolio</li>
                            <li class="mb-2">Included in Merkle tree for batch verification</li>
                            <li>PDF certificate can be generated after issuance</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
