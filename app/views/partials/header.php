<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'CertiMe') ?> — CertiMe</title>
    <meta name="description" content="<?= e($description ?? 'Digital Credentialing Platform — Issue, verify, and share Open Badges 3.0 credentials') ?>">
    <?php if (function_exists('csrfToken')): ?>
    <meta name="csrf-token" content="<?= csrfToken() ?>">
    <?php endif; ?>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- App CSS -->
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">

    <?php if (!empty($extraCss)): ?>
    <?= $extraCss ?>
    <?php endif; ?>
</head>
<body>

<!-- Top Navigation -->
<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= url('') ?>">
            <span class="material-symbols-rounded brand-icon">verified</span>
            <span class="brand-text">CertiMe</span>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="material-symbols-rounded">menu</span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto align-items-center gap-1">
                <li class="nav-item">
                    <a class="nav-link" href="<?= url('') ?>">
                        <span class="material-symbols-rounded nav-icon">home</span>
                        Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= url('verify') ?>">
                        <span class="material-symbols-rounded nav-icon">fact_check</span>
                        Verify
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= url('directory') ?>">
                        <span class="material-symbols-rounded nav-icon">public</span>
                        Directory
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= url('claim') ?>">
                        <span class="material-symbols-rounded nav-icon">pin</span>
                        Claim Badge
                    </a>
                </li>

                <?php if (isLoggedIn()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('portfolio') ?>">
                            <span class="material-symbols-rounded nav-icon">folder_shared</span>
                            Portfolio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('transcript/user/' . ($_SESSION['user_id'] ?? 0)) ?>">
                            <span class="material-symbols-rounded nav-icon">description</span>
                            Transcript
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('coursework') ?>">
                            <span class="material-symbols-rounded nav-icon">school</span>
                            Coursework
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('upload') ?>">
                            <span class="material-symbols-rounded nav-icon">cloud_upload</span>
                            Uploads
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-1" href="#" data-bs-toggle="dropdown" id="toolsDropdown">
                            <span class="material-symbols-rounded nav-icon">handyman</span>
                            Tools
                        </a>
                        <ul class="dropdown-menu shadow-lg border-0">
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="<?= url('badge/builder') ?>">
                                    <span class="material-symbols-rounded" style="font-size:20px">palette</span>
                                    Badge Builder
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="<?= url('pricing') ?>">
                                    <span class="material-symbols-rounded" style="font-size:20px">payments</span>
                                    Plans & Pricing
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="<?= url('team') ?>">
                                    <span class="material-symbols-rounded" style="font-size:20px">group</span>
                                    Team Management
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="<?= url('resume') ?>">
                                    <span class="material-symbols-rounded" style="font-size:20px">description</span>
                                    Digital Resume
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="<?= url('portfolio/settings') ?>">
                                    <span class="material-symbols-rounded" style="font-size:20px">settings</span>
                                    Portfolio Settings
                                </a>
                            </li>
                        </ul>
                    </li>

                    <?php if (isStaff()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-1" href="#" data-bs-toggle="dropdown">
                            <span class="material-symbols-rounded nav-icon">admin_panel_settings</span>
                            Admin
                        </a>
                        <ul class="dropdown-menu shadow-lg border-0">
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="<?= url('admin') ?>">
                                    <span class="material-symbols-rounded" style="font-size:20px">dashboard</span>
                                    Dashboard
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="<?= url('admin/credentials') ?>">
                                    <span class="material-symbols-rounded" style="font-size:20px">workspace_premium</span>
                                    Credentials
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="<?= url('admin/bulk') ?>">
                                    <span class="material-symbols-rounded" style="font-size:20px">upload_file</span>
                                    Bulk Issuance
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="<?= url('admin/renewals') ?>">
                                    <span class="material-symbols-rounded" style="font-size:20px">autorenew</span>
                                    Renewals
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="<?= url('admin/users') ?>">
                                    <span class="material-symbols-rounded" style="font-size:20px">manage_accounts</span>
                                    Users
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="<?= url('admin/keys') ?>">
                                    <span class="material-symbols-rounded" style="font-size:20px">key</span>
                                    Keys
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="<?= url('admin/audit') ?>">
                                    <span class="material-symbols-rounded" style="font-size:20px">history</span>
                                    Audit Log
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="<?= url('admin/endorsements') ?>">
                                    <span class="material-symbols-rounded" style="font-size:20px">thumb_up</span>
                                    Endorsements
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="<?= url('admin/key-rotation') ?>">
                                    <span class="material-symbols-rounded" style="font-size:20px">rotate_right</span>
                                    Key Rotation
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="<?= url('admin/import-badge') ?>">
                                    <span class="material-symbols-rounded" style="font-size:20px">download</span>
                                    Import Badge
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="<?= url('admin/webhooks') ?>">
                                    <span class="material-symbols-rounded" style="font-size:20px">webhook</span>
                                    Webhooks
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="<?= url('admin/api-keys') ?>">
                                    <span class="material-symbols-rounded" style="font-size:20px">api</span>
                                    API Keys
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="<?= url('admin/lti') ?>">
                                    <span class="material-symbols-rounded" style="font-size:20px">cast_for_education</span>
                                    LTI 1.3
                                </a>
                            </li>
                            <?php if (isAdmin()): ?>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="<?= url('admin/tenants') ?>">
                                    <span class="material-symbols-rounded" style="font-size:20px">apartment</span>
                                    Tenants
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="<?= url('admin/system-health') ?>">
                                    <span class="material-symbols-rounded" style="font-size:20px">monitor_heart</span>
                                    System Health
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="<?= url('admin/disputes') ?>">
                                    <span class="material-symbols-rounded" style="font-size:20px">gavel</span>
                                    Disputes
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-1" href="#" data-bs-toggle="dropdown">
                            <span class="material-symbols-rounded">account_circle</span>
                            <?= e($_SESSION['user_username'] ?? 'Account') ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                            <li>
                                <span class="dropdown-item-text small text-muted">
                                    <?= e(ucfirst($_SESSION['user_role'] ?? 'student')) ?>
                                </span>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="<?= url('portfolio') ?>">
                                    <span class="material-symbols-rounded" style="font-size:20px">person</span>
                                    My Portfolio
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="<?= url('logout') ?>">
                                    <span class="material-symbols-rounded" style="font-size:20px">logout</span>
                                    Sign Out
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('login') ?>">Sign In</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary btn-sm rounded-pill px-3" href="<?= url('register') ?>">
                            Get Started
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Flash Messages -->
<?php if ($flash = flash()): ?>
<div class="container mt-3">
    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : e($flash['type']) ?> alert-dismissible fade show material-alert" role="alert">
        <div class="d-flex align-items-center gap-2">
            <span class="material-symbols-rounded">
                <?php
                switch($flash['type']) {
                    case 'success': echo 'check_circle'; break;
                    case 'error': echo 'error'; break;
                    case 'warning': echo 'warning'; break;
                    default: echo 'info'; break;
                }
                ?>
            </span>
            <?= e($flash['message']) ?>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endif; ?>

<!-- Impersonation Banner -->
<?php if (!empty($_SESSION['impersonation'])): ?>
<div class="bg-warning text-dark py-2">
    <div class="container d-flex align-items-center justify-content-between">
        <span>
            <span class="material-symbols-rounded align-middle" style="font-size:18px">supervised_user_circle</span>
            <strong>Impersonating:</strong> <?= e($_SESSION['user_username'] ?? 'Unknown') ?>
        </span>
        <a href="<?= url('admin/stop-impersonation') ?>" class="btn btn-sm btn-dark">
            <span class="material-symbols-rounded align-middle" style="font-size:16px">close</span> Stop Impersonation
        </a>
    </div>
</div>
<?php endif; ?>
