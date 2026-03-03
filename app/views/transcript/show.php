<?php
$title = 'Academic Transcript';
require APP_PATH . '/views/partials/header.php';
$credentials = $credentials ?? [];
$merkleRoot = $merkleRoot ?? null;
$signatureValid = $signatureValid ?? false;
$proofs = $proofs ?? [];
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">
                <span class="material-symbols-rounded align-middle me-1">description</span>
                Academic Transcript
            </h3>
            <p class="text-muted mb-0">
                Cryptographically verifiable credential transcript for 
                <strong><?= e($user['full_name'] ?? $user['username'] ?? 'User') ?></strong>
            </p>
        </div>
    </div>

    <!-- Merkle Root Verification -->
    <?php if ($merkleRoot): ?>
    <div class="material-card mb-4 <?= $signatureValid ? 'border-success' : 'border-warning' ?>" 
         style="border-left: 4px solid <?= $signatureValid ? 'var(--md-success)' : 'var(--md-warning)' ?>">
        <div class="d-flex align-items-start gap-3">
            <span class="material-symbols-rounded" style="font-size:32px;color:<?= $signatureValid ? 'var(--md-success)' : 'var(--md-warning)' ?>">
                <?= $signatureValid ? 'verified_user' : 'gpp_maybe' ?>
            </span>
            <div class="flex-grow-1">
                <h6 class="fw-semibold mb-1">
                    Merkle Tree Integrity: 
                    <span class="<?= $signatureValid ? 'text-success' : 'text-warning' ?>">
                        <?= $signatureValid ? 'Verified' : 'Unverified' ?>
                    </span>
                </h6>
                <p class="text-muted small mb-2">
                    All credentials in this transcript are included in a Merkle tree. 
                    The root hash is signed with Ed25519 for batch integrity verification.
                </p>
                <details>
                    <summary class="small fw-medium cursor-pointer">Technical Details</summary>
                    <div class="bg-body-tertiary rounded p-2 mt-2">
                        <div class="small font-monospace">
                            <div class="mb-1"><strong>Root Hash:</strong> <?= e($merkleRoot['root'] ?? 'N/A') ?></div>
                            <div><strong>Leaf Count:</strong> <?= e($merkleRoot['leaf_count'] ?? count($credentials)) ?></div>
                        </div>
                    </div>
                </details>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Credentials List -->
    <?php if (empty($credentials)): ?>
        <div class="material-card text-center py-5">
            <span class="material-symbols-rounded mb-3" style="font-size:64px;color:var(--md-primary-light)">description</span>
            <h5 class="fw-semibold">No Credentials</h5>
            <p class="text-muted">This transcript is empty.</p>
        </div>
    <?php else: ?>
        <div class="material-card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Credential</th>
                            <th>Type</th>
                            <th>Category</th>
                            <th>Credits</th>
                            <th>Issuer</th>
                            <th>Date</th>
                            <th>Signature</th>
                            <th>Merkle Proof</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalCredits = 0;
                        foreach ($credentials as $i => $c): 
                            $ch = (float)($c['credit_hours'] ?? 0);
                            $totalCredits += $ch;
                        ?>
                        <tr>
                            <td class="text-muted"><?= $i + 1 ?></td>
                            <td>
                                <a href="<?= url('credential/' . e($c['credential_uid'])) ?>" class="text-decoration-none fw-medium">
                                    <?= e($c['course_name']) ?>
                                </a>
                                <?php if (($c['status'] ?? 'active') === 'revoked'): ?>
                                    <span class="badge bg-danger-subtle text-danger rounded-pill ms-1">Revoked</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-info-subtle text-info rounded-pill">
                                    <?= e(ucfirst($c['credential_type'] ?? 'certificate')) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary rounded-pill">
                                    <?= e($c['category'] ?? 'General') ?>
                                </span>
                            </td>
                            <td class="text-muted small"><?= $ch > 0 ? $ch : '—' ?></td>
                            <td class="text-muted small"><?= e($c['issuer_name'] ?? 'CertiMe') ?></td>
                            <td class="text-muted small"><?= date('M j, Y', strtotime($c['issuance_date'])) ?></td>
                            <td>
                                <?php if (!empty($c['badge_jsonld'])): ?>
                                    <span class="text-success" title="Ed25519 signed">
                                        <span class="material-symbols-rounded" style="font-size:18px">check_circle</span>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted" title="Unsigned">
                                        <span class="material-symbols-rounded" style="font-size:18px">remove_circle_outline</span>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php $proof = $proofs[$c['credential_uid']] ?? null; ?>
                                <?php if (!empty($proof)): ?>
                                    <span class="text-success" title="Merkle proof available">
                                        <span class="material-symbols-rounded" style="font-size:18px">account_tree</span>
                                    </span>
                                    <details class="d-inline">
                                        <summary class="small text-primary cursor-pointer ms-1">Show</summary>
                                        <div class="bg-body-tertiary rounded p-2 mt-1">
                                            <pre class="small mb-0" style="font-size:0.65rem;max-height:150px;overflow:auto"><?= e(json_encode($proof, JSON_PRETTY_PRINT)) ?></pre>
                                        </div>
                                    </details>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <?php if ($totalCredits > 0): ?>
                    <tfoot>
                        <tr class="table-light fw-semibold">
                            <td colspan="4" class="text-end">Total Credit Hours:</td>
                            <td><?= $totalCredits ?></td>
                            <td colspan="4"></td>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- Legend -->
    <div class="material-card bg-body-tertiary mt-4">
        <div class="row g-3 small">
            <div class="col-md-4">
                <div class="d-flex align-items-center gap-2">
                    <span class="material-symbols-rounded text-success" style="font-size:18px">check_circle</span>
                    <span>Ed25519 digital signature present and valid</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-center gap-2">
                    <span class="material-symbols-rounded text-success" style="font-size:18px">account_tree</span>
                    <span>Merkle tree inclusion proof available</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-center gap-2">
                    <span class="material-symbols-rounded text-success" style="font-size:18px">verified_user</span>
                    <span>Root hash signature verified</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
