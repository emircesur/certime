<?php require APP_PATH . '/views/partials/header.php'; ?>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="mb-4">
                    <a href="<?= url('upload') ?>" class="text-decoration-none text-muted">
                        <span class="material-symbols-rounded" style="font-size:18px;vertical-align:-3px">arrow_back</span>
                        Back to Uploaded Credentials
                    </a>
                </div>

                <div class="card shadow-sm border-0" style="border-radius:16px">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h2 class="fw-bold mb-1">
                            <span class="material-symbols-rounded" style="font-size:28px;vertical-align:-5px;color:var(--md-primary)">cloud_upload</span>
                            Upload External Credential
                        </h2>
                        <p class="text-muted small mb-0">Add a credential from another provider to your portfolio. It will be clearly marked as "Uploaded — Not issued by CertiMe".</p>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" action="<?= url('upload/store') ?>" enctype="multipart/form-data">
                            <?= csrfField() ?>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Credential Title *</label>
                                <input type="text" name="title" class="form-control" placeholder="e.g. AWS Solutions Architect" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Issuing Organization *</label>
                                <input type="text" name="issuer" class="form-control" placeholder="e.g. Amazon Web Services" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" class="form-control" rows="3" placeholder="Brief description of the credential..."></textarea>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Credential Type</label>
                                    <select name="credential_type" class="form-select">
                                        <option value="certificate">Certificate</option>
                                        <option value="degree">Degree</option>
                                        <option value="diploma">Diploma</option>
                                        <option value="license">License</option>
                                        <option value="badge">Badge</option>
                                        <option value="certification">Professional Certification</option>
                                        <option value="course">Course Completion</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Verification URL</label>
                                    <input type="url" name="external_url" class="form-control" placeholder="https://verify.example.com/...">
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Issue Date</label>
                                    <input type="date" name="issued_date" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Expiration Date</label>
                                    <input type="date" name="expiration_date" class="form-control">
                                    <small class="text-muted">Leave blank if no expiration</small>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Credential File</label>
                                <input type="file" name="credential_file" class="form-control" accept=".pdf,.png,.jpg,.jpeg,.gif,.webp">
                                <small class="text-muted">Upload a PDF, image, or scan of your credential (max 10MB)</small>
                            </div>

                            <div class="alert alert-warning d-flex align-items-center gap-2" style="border-radius:12px">
                                <span class="material-symbols-rounded">info</span>
                                <small>This credential will be marked as <strong>"Uploaded — Not Issued by CertiMe"</strong> to maintain trust and transparency.</small>
                            </div>

                            <div class="d-flex gap-2 mt-3">
                                <button type="submit" class="btn btn-primary rounded-pill px-4">
                                    <span class="material-symbols-rounded" style="font-size:18px;vertical-align:-3px">cloud_upload</span>
                                    Upload Credential
                                </button>
                                <a href="<?= url('upload') ?>" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
