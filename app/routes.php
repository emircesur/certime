<?php
// CertiMe - Route Definitions

// Home
$router->get('', 'HomeController@index');
$router->get('home', 'HomeController@index');

// Credential lookup by query param (for old UIDs with dots that can't be in URL paths)
$router->get('credential/lookup', 'CredentialController@lookup');

// Authentication
$router->get('register', 'AuthController@register');
$router->post('register', 'AuthController@handleRegister');
$router->get('login', 'AuthController@login');
$router->post('login', 'AuthController@handleLogin');
$router->get('logout', 'AuthController@logout');

// Portfolio (authenticated)
$router->get('portfolio', 'PortfolioController@index');
$router->get('portfolio/export', 'PortfolioController@export');

// Credential routes (public)
$router->get('credential/:uid', 'CredentialController@show');
$router->get('credential/:uid/json', 'CredentialController@badgeJson');
$router->get('credential/:uid/badge', 'CredentialController@badge');
$router->get('credential/:uid/badge-image', 'CredentialController@badgeImage');
$router->get('verify', 'CredentialController@verifyForm');
$router->post('verify', 'CredentialController@verifySubmit');

// Endorsement routes
$router->post('credential/:uid/endorse', 'EndorsementController@create');
$router->post('endorsement/:id/approve', 'EndorsementController@approve');
$router->post('endorsement/:id/reject', 'EndorsementController@reject');

// Admin routes
$router->get('admin', 'AdminController@index');
$router->get('admin/users', 'AdminController@users');
$router->post('admin/users/:id/role', 'AdminController@updateRole');
$router->post('admin/users/:id/toggle', 'AdminController@toggleUser');
$router->get('admin/credentials', 'AdminController@credentials');
$router->get('admin/credentials/create', 'AdminController@createCredential');
$router->get('admin/create', 'AdminController@createCredential');
$router->post('admin/credentials/create', 'AdminController@handleCreateCredential');
$router->post('admin/create', 'AdminController@handleCreateCredential');
$router->post('admin/credentials/:uid/revoke', 'AdminController@revokeCredential');
$router->get('admin/keys', 'AdminController@keys');
$router->post('admin/keys/generate', 'AdminController@generatePdfKeys');
$router->post('admin/keys/generate-ed25519', 'AdminController@generateEd25519Keys');
$router->post('admin/keys/upload', 'AdminController@uploadPdfKeys');
$router->get('admin/keys/download', 'AdminController@downloadKeys');
$router->get('admin/audit', 'AdminController@auditLog');

// Agent routes
$router->post('agent/chat', 'AgentController@chat');
$router->post('agent/message', 'AgentController@chat');

// Transcript routes
$router->get('transcript/user/:id', 'TranscriptController@show');

// PDF Download routes
$router->get('credential/:uid/pdf', 'PdfController@download');
$router->get('download/pdf/:uid', 'PdfController@download');

// ─── Plans & Pricing ─────────────────────────────────────────────────────
$router->get('pricing', 'PlanController@pricing');
$router->post('plan/subscribe', 'PlanController@subscribe');
$router->post('plan/cancel', 'PlanController@cancel');

// ─── Team Management ─────────────────────────────────────────────────────
$router->get('team', 'PlanController@team');
$router->post('team/add', 'PlanController@addMember');
$router->post('team/remove/:id', 'PlanController@removeMember');

// ─── Upload External Credentials ─────────────────────────────────────────
$router->get('upload', 'UploadController@index');
$router->get('upload/create', 'UploadController@create');
$router->post('upload/store', 'UploadController@store');
$router->get('upload/:id', 'UploadController@show');
$router->post('upload/:id/delete', 'UploadController@delete');

// ─── Evidence Linking ────────────────────────────────────────────────────
$router->get('credential/:uid/evidence', 'EvidenceController@index');
$router->post('credential/:uid/evidence', 'EvidenceController@store');
$router->post('credential/:uid/evidence/:id/delete', 'EvidenceController@delete');

// ─── Coursework ──────────────────────────────────────────────────────────
$router->get('coursework', 'CourseworkController@index');
$router->get('coursework/create', 'CourseworkController@create');
$router->post('coursework/store', 'CourseworkController@store');
$router->get('coursework/:id/edit', 'CourseworkController@edit');
$router->post('coursework/:id/update', 'CourseworkController@update');
$router->post('coursework/:id/delete', 'CourseworkController@delete');

// ─── Bulk Issuance (Admin/Staff) ─────────────────────────────────────────
$router->get('admin/bulk', 'BulkController@index');
$router->post('admin/bulk/upload', 'BulkController@upload');
$router->post('admin/bulk/process', 'BulkController@process');
$router->get('admin/bulk/job/:id', 'BulkController@show');

// ─── Admin: Edit Credential & Renewals ───────────────────────────────────
$router->get('admin/credentials/:uid/edit', 'AdminController@editCredential');
$router->post('admin/credentials/:uid/edit', 'AdminController@handleEditCredential');
$router->get('admin/renewals', 'AdminController@renewals');
$router->post('admin/credentials/:uid/renew', 'AdminController@renewCredential');

// ─── Admin: Endorsement Management ───────────────────────────────────────
$router->get('admin/endorsements', 'AdminController@endorsements');

// ─── Admin: Cryptographic Key Rotation ───────────────────────────────────
$router->get('admin/key-rotation', 'AdminController@keyRotation');
$router->post('admin/rotate-ed25519', 'AdminController@rotateEd25519');
$router->post('admin/rotate-pdf-keys', 'AdminController@rotatePdfKeys');
$router->get('admin/verify-archived', 'AdminController@verifyWithArchived');

// ─── Admin: Open Badge 3.0 Import ────────────────────────────────────────
$router->get('admin/import-badge', 'AdminController@importBadge');
$router->post('admin/import-badge', 'AdminController@handleImportBadge');

// ─── Visual Badge Builder ────────────────────────────────────────────────
$router->get('badge/builder', 'BadgeBuilderController@index');
$router->post('badge/save', 'BadgeBuilderController@save');
$router->get('badge/list', 'BadgeBuilderController@list');

// ═══════════════════════════════════════════════════════════════════════════
//  NEW FEATURE ROUTES
// ═══════════════════════════════════════════════════════════════════════════

// ─── Super-Admin: Tenant / Institution Management ────────────────────────
$router->get('admin/tenants', 'SuperAdminController@tenants');
$router->get('admin/tenants/create', 'SuperAdminController@createTenant');
$router->post('admin/tenants/create', 'SuperAdminController@handleCreateTenant');
$router->post('admin/tenants/:id/action', 'SuperAdminController@tenantAction');
$router->get('admin/tenants/:id/departments', 'SuperAdminController@tenantDepartments');
$router->post('admin/tenants/:id/departments', 'SuperAdminController@createDepartment');
$router->post('admin/tenants/:id/members', 'SuperAdminController@addTenantMember');

// ─── Super-Admin: Feature Flags ──────────────────────────────────────────
$router->get('admin/feature-flags', 'SuperAdminController@featureFlags');
$router->post('admin/feature-flags/:id/toggle', 'SuperAdminController@toggleFeatureFlag');
$router->post('admin/feature-flags/institution', 'SuperAdminController@setInstitutionFlag');

// ─── Super-Admin: System Health ──────────────────────────────────────────
$router->get('admin/system-health', 'SuperAdminController@systemHealth');

// ─── Super-Admin: Impersonation ──────────────────────────────────────────
$router->post('admin/impersonate/:id', 'SuperAdminController@impersonate');
$router->get('admin/stop-impersonation', 'SuperAdminController@stopImpersonation');
$router->get('admin/impersonation-log', 'SuperAdminController@impersonationLog');

// ─── Super-Admin: CRL Manager ────────────────────────────────────────────
$router->get('admin/crl', 'SuperAdminController@crlManager');
$router->post('admin/crl/revoke', 'SuperAdminController@revokeFromCrl');
$router->post('admin/crl/merkle', 'SuperAdminController@recalculateMerkleTree');

// ─── Super-Admin: Garbage Collector ──────────────────────────────────────
$router->get('admin/garbage-collector', 'SuperAdminController@garbageCollector');
$router->post('admin/garbage-collector/run', 'SuperAdminController@runGarbageCollection');

// ─── Super-Admin: Audit Trail ────────────────────────────────────────────
$router->get('admin/audit-trail', 'SuperAdminController@auditTrail');

// ─── Super-Admin: Disputes ───────────────────────────────────────────────
$router->get('admin/disputes', 'SuperAdminController@disputes');
$router->get('admin/disputes/:id', 'SuperAdminController@disputeDetail');
$router->post('admin/disputes/:id/update', 'SuperAdminController@updateDispute');

// ─── Super-Admin: Invoices ───────────────────────────────────────────────
$router->get('admin/invoices', 'SuperAdminController@invoices');
$router->get('admin/invoices/create', 'SuperAdminController@createInvoice');
$router->post('admin/invoices/create', 'SuperAdminController@handleCreateInvoice');
$router->post('admin/invoices/:id/status', 'SuperAdminController@updateInvoiceStatus');

// ─── Super-Admin: OTP Pending Claims ─────────────────────────────────────
$router->get('admin/otp-pending', 'SuperAdminController@otpPending');

// ─── Super-Admin: Badge Directory Management ─────────────────────────────
$router->get('admin/directory', 'DirectoryController@manage');
$router->post('admin/directory/add', 'DirectoryController@addBadge');
$router->post('admin/directory/:id/remove', 'DirectoryController@removeBadge');

// ─── Super-Admin: Skill Taxonomy Management ──────────────────────────────
$router->get('admin/skills', 'DirectoryController@manageSkills');
$router->post('admin/skills/add', 'DirectoryController@addSkill');
$router->post('admin/skills/link', 'DirectoryController@linkSkill');

// ─── API: Key Management (admin UI) ─────────────────────────────────────
$router->get('admin/api-keys', 'ApiController@keys');
$router->post('admin/api-keys/create', 'ApiController@createKey');
$router->post('admin/api-keys/:id/revoke', 'ApiController@revokeKey');

// ─── API: REST Endpoints (Bearer token auth) ────────────────────────────
$router->get('api/v1/credentials', 'ApiController@listCredentials');
$router->get('api/v1/credentials/:uid', 'ApiController@getCredential');
$router->post('api/v1/credentials', 'ApiController@issueCredential');
$router->post('api/v1/credentials/:uid/revoke', 'ApiController@revokeCredential');
$router->get('api/v1/verify/:uid', 'ApiController@verifyCredential');
$router->get('api/v1/user', 'ApiController@apiUser');

// ─── Webhooks ────────────────────────────────────────────────────────────
$router->get('admin/webhooks', 'WebhookController@index');
$router->get('admin/webhooks/create', 'WebhookController@create');
$router->post('admin/webhooks/store', 'WebhookController@store');
$router->post('admin/webhooks/:id/toggle', 'WebhookController@toggle');
$router->post('admin/webhooks/:id/delete', 'WebhookController@delete');
$router->get('admin/webhooks/:id/events', 'WebhookController@events');
$router->post('admin/webhooks/:id/test', 'WebhookController@test');

// ─── LTI 1.3 Integration ────────────────────────────────────────────────
$router->get('admin/lti', 'LtiController@config');
$router->post('admin/lti/register', 'LtiController@register');
$router->post('admin/lti/:id/delete', 'LtiController@deleteConnection');
$router->get('lti/jwks', 'LtiController@jwks');
$router->post('lti/login', 'LtiController@login');
$router->post('lti/launch', 'LtiController@launch');
$router->get('lti/deeplink', 'LtiController@deeplink');

// ─── OTP Badge Claiming ─────────────────────────────────────────────────
$router->get('claim', 'OtpController@claimForm');
$router->post('claim/verify', 'OtpController@verify');
$router->post('otp/generate', 'OtpController@generate');

// ─── Social Sharing ─────────────────────────────────────────────────────
$router->get('share/:uid/:platform', 'SocialController@share');
$router->get('credential/:uid/share', 'SocialController@sharePage');
$router->get('og/:uid', 'SocialController@openGraph');
$router->get('credential/:uid/embed', 'SocialController@embedPage');

// ─── Digital Resume ─────────────────────────────────────────────────────
$router->get('resume', 'ResumeController@index');
$router->get('resume/json', 'ResumeController@exportJson');
$router->get('resume/pdf', 'ResumeController@exportPdf');

// ─── Public Portfolio ───────────────────────────────────────────────────
$router->get('portfolio/settings', 'PublicPortfolioController@settings');
$router->post('portfolio/settings', 'PublicPortfolioController@saveSettings');
$router->get('p/:slug', 'PublicPortfolioController@show');

// ─── Public Badge Directory ─────────────────────────────────────────────
$router->get('directory', 'DirectoryController@index');
$router->get('directory/skills', 'DirectoryController@skills');
