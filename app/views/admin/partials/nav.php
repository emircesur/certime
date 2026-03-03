<div class="admin-nav mb-4">
    <nav class="nav nav-pills flex-column gap-1">
        <a class="nav-link <?= ($_SERVER['REQUEST_URI'] ?? '') === url('admin') || str_ends_with($_SERVER['REQUEST_URI'] ?? '', '/admin') ? 'active' : '' ?>" 
           href="<?= url('admin') ?>">
            <span class="material-symbols-rounded nav-icon">dashboard</span>
            Dashboard
        </a>
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/credentials') ? 'active' : '' ?>" 
           href="<?= url('admin/credentials') ?>">
            <span class="material-symbols-rounded nav-icon">school</span>
            Credentials
        </a>
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/create') ? 'active' : '' ?>" 
           href="<?= url('admin/create') ?>">
            <span class="material-symbols-rounded nav-icon">add_circle</span>
            Issue New
        </a>
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/users') ? 'active' : '' ?>" 
           href="<?= url('admin/users') ?>">
            <span class="material-symbols-rounded nav-icon">group</span>
            Users
        </a>
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/keys') && !str_contains($_SERVER['REQUEST_URI'] ?? '', '/api-keys') ? 'active' : '' ?>" 
           href="<?= url('admin/keys') ?>">
            <span class="material-symbols-rounded nav-icon">vpn_key</span>
            Signing Keys
        </a>
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/audit') && !str_contains($_SERVER['REQUEST_URI'] ?? '', '/audit-trail') ? 'active' : '' ?>" 
           href="<?= url('admin/audit') ?>">
            <span class="material-symbols-rounded nav-icon">history</span>
            Audit Log
        </a>
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/endorsements') ? 'active' : '' ?>" 
           href="<?= url('admin/endorsements') ?>">
            <span class="material-symbols-rounded nav-icon">thumb_up</span>
            Endorsements
        </a>
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/key-rotation') ? 'active' : '' ?>" 
           href="<?= url('admin/key-rotation') ?>">
            <span class="material-symbols-rounded nav-icon">rotate_right</span>
            Key Rotation
        </a>
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/import-badge') ? 'active' : '' ?>" 
           href="<?= url('admin/import-badge') ?>">
            <span class="material-symbols-rounded nav-icon">download</span>
            Import Badge
        </a>
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/bulk') ? 'active' : '' ?>" 
           href="<?= url('admin/bulk') ?>">
            <span class="material-symbols-rounded nav-icon">upload_file</span>
            Bulk Issuance
        </a>
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/renewals') ? 'active' : '' ?>" 
           href="<?= url('admin/renewals') ?>">
            <span class="material-symbols-rounded nav-icon">autorenew</span>
            Renewals
        </a>

        <!-- ── Integrations ────────────────────────────────────── -->
        <small class="text-muted px-3 mt-3 mb-1 text-uppercase fw-bold" style="font-size:0.65rem;letter-spacing:1px">Integrations</small>
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/webhooks') ? 'active' : '' ?>" 
           href="<?= url('admin/webhooks') ?>">
            <span class="material-symbols-rounded nav-icon">webhook</span>
            Webhooks
        </a>
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/api-keys') ? 'active' : '' ?>" 
           href="<?= url('admin/api-keys') ?>">
            <span class="material-symbols-rounded nav-icon">api</span>
            API Keys
        </a>
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/lti') ? 'active' : '' ?>" 
           href="<?= url('admin/lti') ?>">
            <span class="material-symbols-rounded nav-icon">cast_for_education</span>
            LTI 1.3
        </a>

        <!-- ── Content Management ──────────────────────────────── -->
        <small class="text-muted px-3 mt-3 mb-1 text-uppercase fw-bold" style="font-size:0.65rem;letter-spacing:1px">Content</small>
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/directory') ? 'active' : '' ?>" 
           href="<?= url('admin/directory') ?>">
            <span class="material-symbols-rounded nav-icon">public</span>
            Badge Directory
        </a>
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/skills') ? 'active' : '' ?>" 
           href="<?= url('admin/skills') ?>">
            <span class="material-symbols-rounded nav-icon">psychology</span>
            Skill Taxonomy
        </a>
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/otp-pending') ? 'active' : '' ?>" 
           href="<?= url('admin/otp-pending') ?>">
            <span class="material-symbols-rounded nav-icon">pin</span>
            OTP Claims
        </a>

        <!-- ── Super Admin ─────────────────────────────────────── -->
        <?php if (isAdmin()): ?>
        <small class="text-muted px-3 mt-3 mb-1 text-uppercase fw-bold" style="font-size:0.65rem;letter-spacing:1px">Super Admin</small>
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/tenants') ? 'active' : '' ?>" 
           href="<?= url('admin/tenants') ?>">
            <span class="material-symbols-rounded nav-icon">apartment</span>
            Tenants
        </a>
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/feature-flags') ? 'active' : '' ?>" 
           href="<?= url('admin/feature-flags') ?>">
            <span class="material-symbols-rounded nav-icon">flag</span>
            Feature Flags
        </a>
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/system-health') ? 'active' : '' ?>" 
           href="<?= url('admin/system-health') ?>">
            <span class="material-symbols-rounded nav-icon">monitor_heart</span>
            System Health
        </a>
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/impersonation') ? 'active' : '' ?>" 
           href="<?= url('admin/impersonation-log') ?>">
            <span class="material-symbols-rounded nav-icon">supervised_user_circle</span>
            Impersonation
        </a>
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/crl') ? 'active' : '' ?>" 
           href="<?= url('admin/crl') ?>">
            <span class="material-symbols-rounded nav-icon">block</span>
            CRL Manager
        </a>
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/garbage') ? 'active' : '' ?>" 
           href="<?= url('admin/garbage-collector') ?>">
            <span class="material-symbols-rounded nav-icon">delete_sweep</span>
            Garbage Collector
        </a>
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/audit-trail') ? 'active' : '' ?>" 
           href="<?= url('admin/audit-trail') ?>">
            <span class="material-symbols-rounded nav-icon">manage_search</span>
            Audit Trail
        </a>
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/disputes') ? 'active' : '' ?>" 
           href="<?= url('admin/disputes') ?>">
            <span class="material-symbols-rounded nav-icon">gavel</span>
            Disputes
        </a>
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/invoices') ? 'active' : '' ?>" 
           href="<?= url('admin/invoices') ?>">
            <span class="material-symbols-rounded nav-icon">receipt_long</span>
            Invoices
        </a>
        <?php endif; ?>

        <hr>
        <a class="nav-link" href="<?= url('') ?>">
            <span class="material-symbols-rounded nav-icon">arrow_back</span>
            Back to Site
        </a>
    </nav>
</div>
