<?php
$title = 'Import Open Badge 3.0';
require APP_PATH . '/views/partials/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-2">
            <?php require APP_PATH . '/views/admin/partials/nav.php'; ?>
        </div>
        <div class="col-lg-10">
            <div class="mb-4">
                <h4 class="fw-bold mb-1">
                    <span class="material-symbols-rounded align-middle me-2">download</span>
                    Import Open Badge 3.0
                </h4>
                <p class="text-muted mb-0">Import verifiable credentials earned on other platforms (Credly, Badgr, Canvas, etc.)</p>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="material-card">
                        <form method="POST" action="<?= url('admin/import-badge') ?>">
                            <?= csrfField() ?>

                            <div class="mb-4">
                                <label class="form-label fw-medium">Recipient Email</label>
                                <input type="email" name="recipient_email" class="form-control" required
                                       placeholder="user@example.com"
                                       value="<?= e($_POST['recipient_email'] ?? '') ?>">
                                <div class="form-text">The email address of the user who earned this badge (must already have an account).</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-medium">Badge JSON-LD</label>
                                <textarea name="badge_json" class="form-control font-monospace" rows="16" required
                                          placeholder='Paste the Open Badges 3.0 JSON-LD here...'
                                          style="font-size:0.85rem"><?= e($_POST['badge_json'] ?? '') ?></textarea>
                                <div class="form-text">
                                    Paste the complete JSON-LD of the Open Badge credential. 
                                    You can get this from the badge platform's API or the badge's JSON endpoint.
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="force_import" id="forceImport" value="1">
                                    <label class="form-check-label" for="forceImport">
                                        Force Import (skip OB3 type validation)
                                    </label>
                                    <div class="form-text">Use this if the badge doesn't strictly follow OB3 types but is still valid.</div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary rounded-pill px-4">
                                    <span class="material-symbols-rounded btn-icon">download</span>
                                    Import Badge
                                </button>
                                <button type="button" class="btn btn-outline-secondary rounded-pill" onclick="validateJson()">
                                    <span class="material-symbols-rounded btn-icon">fact_check</span>
                                    Validate JSON
                                </button>
                            </div>
                        </form>

                        <div id="validationResult" class="mt-3" style="display:none"></div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Instructions -->
                    <div class="material-card bg-body-tertiary">
                        <h6 class="fw-semibold mb-3">
                            <span class="material-symbols-rounded align-middle me-1">help</span>
                            How to Import
                        </h6>
                        <ol class="small text-muted mb-0" style="padding-left:1.2rem">
                            <li class="mb-2">Go to the badge platform (Credly, Badgr, Canvas, etc.)</li>
                            <li class="mb-2">Find the badge/credential and look for a "JSON" or "API" link</li>
                            <li class="mb-2">Copy the complete JSON-LD payload</li>
                            <li class="mb-2">Paste it in the text field on the left</li>
                            <li class="mb-2">Enter the recipient's email (must already have an account here)</li>
                            <li class="mb-0">Click Import — the badge will appear in their portfolio</li>
                        </ol>
                    </div>

                    <!-- Supported Formats -->
                    <div class="material-card mt-3">
                        <h6 class="fw-semibold mb-3">
                            <span class="material-symbols-rounded align-middle me-1">verified</span>
                            Supported Formats
                        </h6>
                        <div class="d-flex flex-column gap-2">
                            <div class="d-flex align-items-center gap-2 small">
                                <span class="badge bg-success">OB 3.0</span>
                                Open Badges 3.0 (VerifiableCredential)
                            </div>
                            <div class="d-flex align-items-center gap-2 small">
                                <span class="badge bg-success">W3C VC</span>
                                W3C Verifiable Credentials
                            </div>
                            <div class="d-flex align-items-center gap-2 small">
                                <span class="badge bg-warning text-dark">OB 2.0</span>
                                Open Badges 2.0 (with Force Import)
                            </div>
                        </div>
                    </div>

                    <!-- Example -->
                    <div class="material-card mt-3">
                        <h6 class="fw-semibold mb-3">
                            <span class="material-symbols-rounded align-middle me-1">code</span>
                            Example OB3 JSON-LD
                        </h6>
                        <button class="btn btn-sm btn-outline-primary rounded-pill mb-2" onclick="fillExample()">
                            Fill Example
                        </button>
                        <pre class="small bg-body-tertiary p-2 rounded mb-0" style="font-size:0.75rem;max-height:200px;overflow:auto">{
  "@context": [
    "https://www.w3.org/2018/credentials/v1",
    "https://purl.imsglobal.org/spec/ob/v3p0/context-3.0.3.json"
  ],
  "type": ["VerifiableCredential", "OpenBadgeCredential"],
  "issuer": {
    "type": ["Profile"],
    "name": "Example University"
  },
  "issuanceDate": "2024-01-15T00:00:00Z",
  "credentialSubject": {
    "type": ["AchievementSubject"],
    "achievement": {
      "type": ["Achievement"],
      "name": "Web Development Certificate",
      "description": "Completed the full-stack web dev program"
    }
  }
}</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function validateJson() {
    const textarea = document.querySelector('textarea[name="badge_json"]');
    const resultDiv = document.getElementById('validationResult');
    resultDiv.style.display = 'block';

    try {
        const data = JSON.parse(textarea.value);
        const types = Array.isArray(data.type) ? data.type : [data.type || ''];
        const isOB3 = types.some(t => ['VerifiableCredential', 'OpenBadgeCredential', 'AchievementCredential'].includes(t));
        const hasSubject = !!data.credentialSubject;
        const hasIssuer = !!data.issuer;
        const hasContext = !!data['@context'];

        let html = '<div class="alert alert-' + (isOB3 ? 'success' : 'warning') + '">';
        html += '<strong>' + (isOB3 ? 'Valid OB3 Credential' : 'Non-standard format') + '</strong><br>';
        html += '<small>Context: ' + (hasContext ? '✅' : '❌') + ' | ';
        html += 'Types: ' + types.join(', ') + ' | ';
        html += 'Subject: ' + (hasSubject ? '✅' : '❌') + ' | ';
        html += 'Issuer: ' + (hasIssuer ? '✅' : '❌') + '</small>';
        
        if (hasSubject && data.credentialSubject.achievement) {
            html += '<br><small>Achievement: ' + (data.credentialSubject.achievement.name || 'unnamed') + '</small>';
        }
        html += '</div>';
        resultDiv.innerHTML = html;
    } catch (e) {
        resultDiv.innerHTML = '<div class="alert alert-danger"><strong>Invalid JSON:</strong> ' + e.message + '</div>';
    }
}

function fillExample() {
    const example = {
        "@context": ["https://www.w3.org/2018/credentials/v1", "https://purl.imsglobal.org/spec/ob/v3p0/context-3.0.3.json"],
        "type": ["VerifiableCredential", "OpenBadgeCredential"],
        "issuer": {"type": ["Profile"], "name": "Example University"},
        "issuanceDate": new Date().toISOString(),
        "credentialSubject": {
            "type": ["AchievementSubject"],
            "achievement": {"type": ["Achievement"], "name": "Sample Imported Badge", "description": "This is an example imported badge."}
        }
    };
    document.querySelector('textarea[name="badge_json"]').value = JSON.stringify(example, null, 2);
}
</script>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
