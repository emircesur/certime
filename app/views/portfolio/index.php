<?php
$title = 'My Portfolio';
require APP_PATH . '/views/partials/header.php';
$user = $user ?? [];
$credentials = $credentials ?? [];
?>

<div class="container py-4">
    <!-- Profile Header -->
    <div class="material-card mb-4">
        <div class="d-flex flex-wrap align-items-center gap-4">
            <div class="user-avatar-lg">
                <?= strtoupper(substr($user['username'] ?? 'U', 0, 1)) ?>
            </div>
            <div class="flex-grow-1">
                <h3 class="fw-bold mb-1"><?= e($user['full_name'] ?? $user['username'] ?? 'User') ?></h3>
                <p class="text-muted mb-1">@<?= e($user['username'] ?? '') ?></p>
                <?php if (!empty($user['bio'])): ?>
                    <p class="mb-0"><?= e($user['bio']) ?></p>
                <?php endif; ?>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= url('portfolio/export') ?>" class="btn btn-outline-primary rounded-pill">
                    <span class="material-symbols-rounded btn-icon">download</span>
                    Export JSON
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Credentials Column -->
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">
                    <span class="material-symbols-rounded align-middle me-1">school</span>
                    My Credentials (<?= count($credentials) ?>)
                </h5>
            </div>

            <?php if (empty($credentials)): ?>
                <div class="material-card text-center py-5">
                    <span class="material-symbols-rounded mb-3" style="font-size:64px;color:var(--md-primary-light)">workspace_premium</span>
                    <h5 class="fw-semibold">No Credentials Yet</h5>
                    <p class="text-muted">Your digital credentials will appear here once issued.</p>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($credentials as $c): ?>
                    <div class="col-md-6">
                        <div class="credential-grid-card">
                            <div class="d-flex align-items-start gap-3 mb-3">
                                <div class="credential-mini-badge">
                                    <span class="material-symbols-rounded" style="font-size:24px;color:white">workspace_premium</span>
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <h6 class="fw-semibold mb-1 text-truncate">
                                        <a href="<?= url('credential/' . e($c['credential_uid'])) ?>" class="text-decoration-none">
                                            <?= e($c['course_name']) ?>
                                        </a>
                                    </h6>
                                    <div class="text-muted small"><?= e($c['issuer_name'] ?? 'CertiMe') ?></div>
                                </div>
                            </div>

                            <?php if (!empty($c['description'])): ?>
                                <p class="text-muted small mb-2 line-clamp-2"><?= e($c['description']) ?></p>
                            <?php endif; ?>

                            <?php if (!empty($c['skills'])): ?>
                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    <?php foreach (array_slice(array_map('trim', explode(',', $c['skills'])), 0, 3) as $skill): ?>
                                        <span class="badge bg-primary-subtle text-primary rounded-pill" style="font-size:0.7rem"><?= e($skill) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top">
                                <small class="text-muted"><?= date('M j, Y', strtotime($c['issuance_date'])) ?></small>
                                <div class="d-flex gap-1">
                                    <?php if (($c['status'] ?? 'active') === 'revoked'): ?>
                                        <span class="badge bg-danger-subtle text-danger rounded-pill">Revoked</span>
                                    <?php else: ?>
                                        <a href="<?= url('credential/' . e($c['credential_uid']) . '/pdf') ?>" 
                                           class="btn btn-sm btn-outline-secondary rounded-circle p-1" title="Download PDF">
                                            <span class="material-symbols-rounded" style="font-size:18px">picture_as_pdf</span>
                                        </a>
                                        <?php
                                        $linkedinUrl = 'https://www.linkedin.com/profile/add?startTask=CERTIFICATION_NAME'
                                            . '&name=' . urlencode($c['course_name'])
                                            . '&organizationName=' . urlencode($c['issuer_name'] ?? 'CertiMe')
                                            . '&issueYear=' . date('Y', strtotime($c['issuance_date']))
                                            . '&issueMonth=' . date('n', strtotime($c['issuance_date']))
                                            . '&certUrl=' . urlencode(absUrl('credential/' . $c['credential_uid']))
                                            . '&certId=' . urlencode($c['credential_uid']);
                                        ?>
                                        <a href="<?= e($linkedinUrl) ?>" target="_blank"
                                           class="btn btn-sm btn-outline-primary rounded-circle p-1" title="Add to LinkedIn">
                                            <span class="material-symbols-rounded" style="font-size:18px">share</span>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Stats -->
            <div class="material-card mb-4">
                <h6 class="fw-semibold mb-3">Portfolio Stats</h6>
                <div class="d-flex justify-content-around text-center">
                    <div>
                        <div class="fs-4 fw-bold text-primary"><?= count($credentials) ?></div>
                        <div class="text-muted small">Credentials</div>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-success">
                            <?= count(array_filter($credentials, fn($c) => !empty($c['badge_jsonld']))) ?>
                        </div>
                        <div class="text-muted small">Verified</div>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-secondary">
                            <?php
                            $allSkills = [];
                            foreach ($credentials as $c) {
                                if (!empty($c['skills'])) {
                                    $allSkills = array_merge($allSkills, array_map('trim', explode(',', $c['skills'])));
                                }
                            }
                            echo count(array_unique($allSkills));
                            ?>
                        </div>
                        <div class="text-muted small">Skills</div>
                    </div>
                </div>
            </div>

            <!-- Skills Cloud -->
            <?php if (!empty($allSkills)): ?>
            <div class="material-card mb-4">
                <h6 class="fw-semibold mb-3">Skills</h6>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach (array_count_values($allSkills) as $skill => $count): ?>
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">
                            <?= e($skill) ?>
                            <?php if ($count > 1): ?>
                                <span class="badge bg-primary rounded-pill ms-1"><?= $count ?></span>
                            <?php endif; ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- AI Chat -->
            <div class="material-card">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="material-symbols-rounded" style="font-size:24px;color:#8b5cf6">smart_toy</span>
                    <h6 class="fw-semibold mb-0">AI Career Advisor</h6>
                </div>
                <div id="chatMessages" class="chat-messages mb-3">
                    <div class="chat-bubble assistant">
                        Hi! I'm your AI career advisor. Ask me about your credentials, career paths, or skill development.
                    </div>
                </div>
                <form id="chatForm" class="d-flex gap-2">
                    <input type="text" class="form-control rounded-pill" id="chatInput" 
                           placeholder="Ask about your career..." autocomplete="off">
                    <button type="submit" class="btn btn-primary rounded-circle p-2" id="chatSend">
                        <span class="material-symbols-rounded" style="font-size:20px">send</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatForm = document.getElementById('chatForm');
    const chatInput = document.getElementById('chatInput');
    const chatMessages = document.getElementById('chatMessages');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    chatForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const msg = chatInput.value.trim();
        if (!msg) return;

        // Add user message
        chatMessages.innerHTML += `<div class="chat-bubble user">${escapeHtml(msg)}</div>`;
        chatInput.value = '';
        chatMessages.scrollTop = chatMessages.scrollHeight;

        // Loading
        const loadingId = 'loading-' + Date.now();
        chatMessages.innerHTML += `<div class="chat-bubble assistant" id="${loadingId}"><span class="typing-dots"><span>.</span><span>.</span><span>.</span></span></div>`;
        chatMessages.scrollTop = chatMessages.scrollHeight;

        try {
            const res = await fetch('<?= url('agent/message') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify({ message: msg })
            });
            const data = await res.json();
            document.getElementById(loadingId).innerHTML = data.response || 'Sorry, I could not process that.';
        } catch (err) {
            document.getElementById(loadingId).innerHTML = 'Connection error. Please try again.';
        }
        chatMessages.scrollTop = chatMessages.scrollHeight;
    });

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
</script>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
