<?php
/**
 * CertiMe - Database Handler
 * SQLite with DELETE journal mode (shared hosting / NFS safe)
 */
class Database {
    private static ?PDO $instance = null;

    private function __construct() {}

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            try {
                $dsn = 'sqlite:' . DB_PATH;
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];
                self::$instance = new PDO($dsn, null, null, $options);
                // Use DELETE journal mode - safe for NFS/shared hosting
                self::$instance->exec('PRAGMA journal_mode = DELETE');
                // Enable foreign keys
                self::$instance->exec('PRAGMA foreign_keys = ON');
                // Optimize for read-heavy workloads
                self::$instance->exec('PRAGMA cache_size = -2000');
            } catch (PDOException $e) {
                if (ENVIRONMENT === 'development') {
                    die('Database connection failed: ' . $e->getMessage());
                }
                die('Database connection failed. Please check configuration.');
            }
        }
        return self::$instance;
    }

    /**
     * Initialize database tables
     */
    public static function setup(): void {
        $pdo = self::getInstance();
        try {
            $pdo->beginTransaction();
            
            $commands = [
                "CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    username TEXT NOT NULL UNIQUE,
                    email TEXT NOT NULL UNIQUE,
                    password_hash TEXT NOT NULL,
                    role TEXT NOT NULL DEFAULT 'student' CHECK(role IN ('student', 'issuer', 'designer', 'viewer', 'moderator', 'admin')),
                    full_name TEXT DEFAULT '',
                    bio TEXT DEFAULT '',
                    avatar_url TEXT DEFAULT '',
                    is_active INTEGER DEFAULT 1,
                    last_login DATETIME,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",

                "CREATE TABLE IF NOT EXISTS credentials (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    credential_uid TEXT NOT NULL UNIQUE,
                    course_name TEXT NOT NULL,
                    description TEXT,
                    issuer_name TEXT NOT NULL,
                    category TEXT DEFAULT 'general',
                    skills TEXT DEFAULT '',
                    credential_type TEXT DEFAULT 'certificate',
                    credit_hours REAL DEFAULT 0,
                    issuance_date DATETIME NOT NULL,
                    expiration_date DATETIME,
                    status TEXT DEFAULT 'active' CHECK(status IN ('active', 'revoked', 'expired')),
                    badge_jsonld TEXT NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
                )",

                "CREATE TABLE IF NOT EXISTS endorsements (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    credential_id INTEGER NOT NULL,
                    endorser_name TEXT NOT NULL,
                    endorser_email TEXT NOT NULL,
                    endorser_org TEXT DEFAULT '',
                    endorser_title TEXT DEFAULT '',
                    comment TEXT NOT NULL,
                    signature TEXT DEFAULT '',
                    status TEXT DEFAULT 'pending' CHECK(status IN ('pending', 'approved', 'rejected')),
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (credential_id) REFERENCES credentials (id) ON DELETE CASCADE
                )",

                "CREATE TABLE IF NOT EXISTS audit_log (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER,
                    action TEXT NOT NULL,
                    details TEXT,
                    ip_address TEXT,
                    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
                )",

                "CREATE INDEX IF NOT EXISTS idx_credentials_user ON credentials(user_id)",
                "CREATE INDEX IF NOT EXISTS idx_credentials_uid ON credentials(credential_uid)",
                "CREATE INDEX IF NOT EXISTS idx_credentials_status ON credentials(status)",
                "CREATE INDEX IF NOT EXISTS idx_endorsements_credential ON endorsements(credential_id)",
                "CREATE INDEX IF NOT EXISTS idx_audit_user ON audit_log(user_id)",
                "CREATE INDEX IF NOT EXISTS idx_audit_timestamp ON audit_log(timestamp)",

                // --- New tables for plans, uploads, evidence, bulk, coursework ---
                "CREATE TABLE IF NOT EXISTS plans (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    slug TEXT NOT NULL UNIQUE,
                    name TEXT NOT NULL,
                    type TEXT NOT NULL DEFAULT 'user' CHECK(type IN ('user','team','institution')),
                    price_monthly REAL DEFAULT 0,
                    price_yearly REAL DEFAULT 0,
                    max_credentials INTEGER DEFAULT 10,
                    max_users INTEGER DEFAULT 1,
                    features TEXT DEFAULT '{}',
                    is_active INTEGER DEFAULT 1,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",

                "CREATE TABLE IF NOT EXISTS subscriptions (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    plan_id INTEGER NOT NULL,
                    team_name TEXT DEFAULT '',
                    billing_cycle TEXT DEFAULT 'monthly' CHECK(billing_cycle IN ('monthly','yearly','lifetime')),
                    status TEXT DEFAULT 'active' CHECK(status IN ('active','cancelled','past_due','trialing')),
                    stripe_customer_id TEXT DEFAULT '',
                    stripe_subscription_id TEXT DEFAULT '',
                    current_period_start DATETIME,
                    current_period_end DATETIME,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (plan_id) REFERENCES plans(id)
                )",

                "CREATE TABLE IF NOT EXISTS team_members (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    subscription_id INTEGER NOT NULL,
                    user_id INTEGER NOT NULL,
                    role TEXT DEFAULT 'member' CHECK(role IN ('owner','admin','member')),
                    invited_email TEXT DEFAULT '',
                    status TEXT DEFAULT 'active' CHECK(status IN ('active','invited','removed')),
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE CASCADE,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )",

                "CREATE TABLE IF NOT EXISTS uploaded_credentials (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    title TEXT NOT NULL,
                    issuer_name TEXT NOT NULL,
                    description TEXT DEFAULT '',
                    credential_type TEXT DEFAULT 'certificate',
                    category TEXT DEFAULT 'general',
                    issue_date DATETIME,
                    expiration_date DATETIME,
                    file_path TEXT DEFAULT '',
                    file_type TEXT DEFAULT '',
                    external_url TEXT DEFAULT '',
                    verification_url TEXT DEFAULT '',
                    is_verified INTEGER DEFAULT 0,
                    status TEXT DEFAULT 'active',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )",

                "CREATE TABLE IF NOT EXISTS evidence (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    credential_id INTEGER,
                    uploaded_credential_id INTEGER,
                    user_id INTEGER NOT NULL,
                    title TEXT NOT NULL,
                    description TEXT DEFAULT '',
                    evidence_type TEXT DEFAULT 'url' CHECK(evidence_type IN ('url','file','github','pdf','image')),
                    url TEXT DEFAULT '',
                    file_path TEXT DEFAULT '',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (credential_id) REFERENCES credentials(id) ON DELETE CASCADE,
                    FOREIGN KEY (uploaded_credential_id) REFERENCES uploaded_credentials(id) ON DELETE CASCADE,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )",

                "CREATE TABLE IF NOT EXISTS coursework (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    credential_id INTEGER,
                    title TEXT NOT NULL,
                    course_code TEXT DEFAULT '',
                    semester TEXT DEFAULT '',
                    grade TEXT DEFAULT '',
                    grade_points REAL DEFAULT 0,
                    credit_hours REAL DEFAULT 0,
                    status TEXT DEFAULT 'completed' CHECK(status IN ('in_progress','completed','withdrawn','incomplete')),
                    instructor TEXT DEFAULT '',
                    notes TEXT DEFAULT '',
                    completed_at DATETIME,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (credential_id) REFERENCES credentials(id) ON DELETE SET NULL
                )",

                "CREATE TABLE IF NOT EXISTS bulk_jobs (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    filename TEXT NOT NULL,
                    total_rows INTEGER DEFAULT 0,
                    processed_rows INTEGER DEFAULT 0,
                    success_count INTEGER DEFAULT 0,
                    error_count INTEGER DEFAULT 0,
                    status TEXT DEFAULT 'pending' CHECK(status IN ('pending','processing','completed','failed')),
                    errors_log TEXT DEFAULT '[]',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    completed_at DATETIME,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )",

                "CREATE TABLE IF NOT EXISTS renewal_reminders (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    credential_id INTEGER NOT NULL,
                    reminder_date DATETIME NOT NULL,
                    sent INTEGER DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (credential_id) REFERENCES credentials(id) ON DELETE CASCADE
                )",

                "CREATE INDEX IF NOT EXISTS idx_subscriptions_user ON subscriptions(user_id)",
                "CREATE INDEX IF NOT EXISTS idx_team_members_sub ON team_members(subscription_id)",
                "CREATE INDEX IF NOT EXISTS idx_uploaded_creds_user ON uploaded_credentials(user_id)",
                "CREATE INDEX IF NOT EXISTS idx_evidence_cred ON evidence(credential_id)",
                "CREATE INDEX IF NOT EXISTS idx_coursework_user ON coursework(user_id)",
                "CREATE INDEX IF NOT EXISTS idx_bulk_jobs_user ON bulk_jobs(user_id)",

                // ============================================================
                // NEW TABLES: Institutions, Departments, Feature Flags, etc.
                // ============================================================

                "CREATE TABLE IF NOT EXISTS institutions (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    slug TEXT NOT NULL UNIQUE,
                    owner_user_id INTEGER,
                    logo_url TEXT DEFAULT '',
                    domain TEXT DEFAULT '',
                    status TEXT DEFAULT 'active' CHECK(status IN ('active','suspended','terminated')),
                    plan_id INTEGER,
                    billing_email TEXT DEFAULT '',
                    settings TEXT DEFAULT '{}',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (owner_user_id) REFERENCES users(id) ON DELETE SET NULL,
                    FOREIGN KEY (plan_id) REFERENCES plans(id)
                )",

                "CREATE TABLE IF NOT EXISTS departments (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    institution_id INTEGER NOT NULL,
                    name TEXT NOT NULL,
                    slug TEXT NOT NULL,
                    description TEXT DEFAULT '',
                    head_user_id INTEGER,
                    is_active INTEGER DEFAULT 1,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (institution_id) REFERENCES institutions(id) ON DELETE CASCADE,
                    FOREIGN KEY (head_user_id) REFERENCES users(id) ON DELETE SET NULL
                )",

                "CREATE TABLE IF NOT EXISTS institution_members (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    institution_id INTEGER NOT NULL,
                    department_id INTEGER,
                    user_id INTEGER NOT NULL,
                    role TEXT DEFAULT 'viewer' CHECK(role IN ('owner','issuer','designer','viewer')),
                    permissions TEXT DEFAULT '{}',
                    status TEXT DEFAULT 'active' CHECK(status IN ('active','invited','removed')),
                    invited_email TEXT DEFAULT '',
                    invite_token TEXT DEFAULT '',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (institution_id) REFERENCES institutions(id) ON DELETE CASCADE,
                    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )",

                "CREATE TABLE IF NOT EXISTS feature_flags (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    institution_id INTEGER,
                    flag_name TEXT NOT NULL,
                    is_enabled INTEGER DEFAULT 0,
                    description TEXT DEFAULT '',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",

                "CREATE TABLE IF NOT EXISTS webhooks (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    institution_id INTEGER,
                    user_id INTEGER NOT NULL,
                    url TEXT NOT NULL,
                    secret TEXT DEFAULT '',
                    events TEXT DEFAULT '[]',
                    is_active INTEGER DEFAULT 1,
                    last_triggered DATETIME,
                    failure_count INTEGER DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (institution_id) REFERENCES institutions(id) ON DELETE CASCADE,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )",

                "CREATE TABLE IF NOT EXISTS webhook_events (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    webhook_id INTEGER NOT NULL,
                    event_type TEXT NOT NULL,
                    payload TEXT NOT NULL,
                    response_code INTEGER,
                    response_body TEXT DEFAULT '',
                    status TEXT DEFAULT 'pending' CHECK(status IN ('pending','sent','failed')),
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (webhook_id) REFERENCES webhooks(id) ON DELETE CASCADE
                )",

                "CREATE TABLE IF NOT EXISTS otp_claims (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    credential_id INTEGER NOT NULL,
                    email TEXT NOT NULL,
                    otp_code TEXT NOT NULL,
                    claimed INTEGER DEFAULT 0,
                    claimed_by_user_id INTEGER,
                    expires_at DATETIME NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (credential_id) REFERENCES credentials(id) ON DELETE CASCADE,
                    FOREIGN KEY (claimed_by_user_id) REFERENCES users(id) ON DELETE SET NULL
                )",

                "CREATE TABLE IF NOT EXISTS skill_taxonomy (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    code TEXT NOT NULL UNIQUE,
                    name TEXT NOT NULL,
                    framework TEXT DEFAULT 'custom' CHECK(framework IN ('custom','esco','lightcast','onet')),
                    category TEXT DEFAULT '',
                    description TEXT DEFAULT '',
                    parent_code TEXT DEFAULT '',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",

                "CREATE TABLE IF NOT EXISTS credential_skills (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    credential_id INTEGER NOT NULL,
                    skill_id INTEGER NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (credential_id) REFERENCES credentials(id) ON DELETE CASCADE,
                    FOREIGN KEY (skill_id) REFERENCES skill_taxonomy(id) ON DELETE CASCADE
                )",

                "CREATE TABLE IF NOT EXISTS badge_directory (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    institution_id INTEGER,
                    title TEXT NOT NULL,
                    description TEXT DEFAULT '',
                    category TEXT DEFAULT 'general',
                    skills TEXT DEFAULT '',
                    badge_image_url TEXT DEFAULT '',
                    course_url TEXT DEFAULT '',
                    provider_name TEXT DEFAULT '',
                    is_featured INTEGER DEFAULT 0,
                    is_active INTEGER DEFAULT 1,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (institution_id) REFERENCES institutions(id) ON DELETE SET NULL
                )",

                "CREATE TABLE IF NOT EXISTS disputes (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    reporter_user_id INTEGER,
                    reporter_email TEXT DEFAULT '',
                    target_type TEXT NOT NULL CHECK(target_type IN ('institution','badge','user','credential')),
                    target_id INTEGER NOT NULL,
                    reason TEXT NOT NULL,
                    description TEXT DEFAULT '',
                    evidence_urls TEXT DEFAULT '',
                    status TEXT DEFAULT 'open' CHECK(status IN ('open','under_review','resolved','dismissed')),
                    assigned_admin_id INTEGER,
                    resolution_notes TEXT DEFAULT '',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    resolved_at DATETIME,
                    FOREIGN KEY (reporter_user_id) REFERENCES users(id) ON DELETE SET NULL,
                    FOREIGN KEY (assigned_admin_id) REFERENCES users(id) ON DELETE SET NULL
                )",

                "CREATE TABLE IF NOT EXISTS invoices (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    institution_id INTEGER,
                    user_id INTEGER,
                    invoice_number TEXT NOT NULL UNIQUE,
                    amount REAL NOT NULL,
                    currency TEXT DEFAULT 'USD',
                    description TEXT DEFAULT '',
                    line_items TEXT DEFAULT '[]',
                    discount_percent REAL DEFAULT 0,
                    discount_amount REAL DEFAULT 0,
                    tax_amount REAL DEFAULT 0,
                    total_amount REAL NOT NULL,
                    status TEXT DEFAULT 'draft' CHECK(status IN ('draft','sent','paid','overdue','cancelled')),
                    due_date DATETIME,
                    paid_at DATETIME,
                    payment_method TEXT DEFAULT '',
                    notes TEXT DEFAULT '',
                    created_by INTEGER,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (institution_id) REFERENCES institutions(id) ON DELETE SET NULL,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
                    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
                )",

                "CREATE TABLE IF NOT EXISTS revocation_list (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    credential_uid TEXT NOT NULL,
                    reason TEXT NOT NULL,
                    revoked_by INTEGER,
                    merkle_recalculated INTEGER DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (revoked_by) REFERENCES users(id) ON DELETE SET NULL
                )",

                "CREATE TABLE IF NOT EXISTS impersonation_log (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    admin_user_id INTEGER NOT NULL,
                    target_user_id INTEGER NOT NULL,
                    reason TEXT DEFAULT '',
                    started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    ended_at DATETIME,
                    actions_performed TEXT DEFAULT '[]',
                    ip_address TEXT DEFAULT '',
                    FOREIGN KEY (admin_user_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (target_user_id) REFERENCES users(id) ON DELETE CASCADE
                )",

                "CREATE TABLE IF NOT EXISTS lti_connections (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    institution_id INTEGER,
                    platform_name TEXT NOT NULL,
                    client_id TEXT NOT NULL,
                    deployment_id TEXT DEFAULT '',
                    auth_login_url TEXT DEFAULT '',
                    auth_token_url TEXT DEFAULT '',
                    jwks_url TEXT DEFAULT '',
                    platform_jwks TEXT DEFAULT '',
                    tool_private_key TEXT DEFAULT '',
                    tool_public_key TEXT DEFAULT '',
                    is_active INTEGER DEFAULT 1,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (institution_id) REFERENCES institutions(id) ON DELETE CASCADE
                )",

                "CREATE TABLE IF NOT EXISTS api_keys (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    institution_id INTEGER,
                    key_hash TEXT NOT NULL UNIQUE,
                    key_prefix TEXT NOT NULL,
                    name TEXT DEFAULT 'API Key',
                    scopes TEXT DEFAULT '[]',
                    last_used DATETIME,
                    is_active INTEGER DEFAULT 1,
                    expires_at DATETIME,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (institution_id) REFERENCES institutions(id) ON DELETE SET NULL
                )",

                "CREATE TABLE IF NOT EXISTS system_metrics (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    metric_name TEXT NOT NULL,
                    metric_value REAL NOT NULL,
                    metadata TEXT DEFAULT '{}',
                    recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",

                // New indexes
                "CREATE INDEX IF NOT EXISTS idx_institutions_owner ON institutions(owner_user_id)",
                "CREATE INDEX IF NOT EXISTS idx_departments_inst ON departments(institution_id)",
                "CREATE INDEX IF NOT EXISTS idx_inst_members_inst ON institution_members(institution_id)",
                "CREATE INDEX IF NOT EXISTS idx_inst_members_user ON institution_members(user_id)",
                "CREATE INDEX IF NOT EXISTS idx_feature_flags_inst ON feature_flags(institution_id)",
                "CREATE INDEX IF NOT EXISTS idx_webhooks_user ON webhooks(user_id)",
                "CREATE INDEX IF NOT EXISTS idx_otp_claims_cred ON otp_claims(credential_id)",
                "CREATE INDEX IF NOT EXISTS idx_otp_claims_email ON otp_claims(email)",
                "CREATE INDEX IF NOT EXISTS idx_skill_taxonomy_code ON skill_taxonomy(code)",
                "CREATE INDEX IF NOT EXISTS idx_credential_skills_cred ON credential_skills(credential_id)",
                "CREATE INDEX IF NOT EXISTS idx_badge_directory_active ON badge_directory(is_active)",
                "CREATE INDEX IF NOT EXISTS idx_disputes_status ON disputes(status)",
                "CREATE INDEX IF NOT EXISTS idx_invoices_inst ON invoices(institution_id)",
                "CREATE INDEX IF NOT EXISTS idx_revocation_list_uid ON revocation_list(credential_uid)",
                "CREATE INDEX IF NOT EXISTS idx_impersonation_admin ON impersonation_log(admin_user_id)",
                "CREATE INDEX IF NOT EXISTS idx_api_keys_hash ON api_keys(key_hash)",
                "CREATE INDEX IF NOT EXISTS idx_system_metrics_name ON system_metrics(metric_name)",
            ];

            foreach ($commands as $command) {
                $pdo->exec($command);
            }
            
            // Auto-migrate: add any missing columns to existing tables
            self::autoMigrate($pdo);

            // Seed default plans if empty
            self::seedPlans($pdo);

            // Seed skill taxonomy
            self::seedSkillTaxonomy($pdo);

            // Seed default feature flags
            self::seedFeatureFlags($pdo);
            
            $pdo->commit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            if (ENVIRONMENT === 'development') {
                die('Database setup failed: ' . $e->getMessage());
            }
            die('Database setup failed. Please check your configuration.');
        }
    }

    /**
     * Log an action to the audit log
     */
    public static function audit(string $action, string $details = '', ?int $userId = null): void {
        try {
            $pdo = self::getInstance();
            $stmt = $pdo->prepare("INSERT INTO audit_log (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $userId ?? currentUserId(),
                $action,
                $details,
                $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
        } catch (\Exception $e) {
            error_log("Audit log failed: " . $e->getMessage());
        }
    }

    /**
     * Auto-migrate: add missing columns to existing tables
     */
    private static function autoMigrate(PDO $pdo): void {
        $migrations = [
            'users' => [
                'full_name' => "TEXT DEFAULT ''",
                'bio' => "TEXT DEFAULT ''",
                'avatar_url' => "TEXT DEFAULT ''",
                'is_active' => "INTEGER DEFAULT 1",
                'last_login' => "DATETIME",
                'updated_at' => "DATETIME",
                'plan_id' => "INTEGER DEFAULT NULL",
                'institution_name' => "TEXT DEFAULT ''",
                'institution_id' => "INTEGER DEFAULT NULL",
                'department_id' => "INTEGER DEFAULT NULL",
                'otp_token' => "TEXT DEFAULT ''",
                'portfolio_slug' => "TEXT DEFAULT ''",
                'portfolio_public' => "INTEGER DEFAULT 0",
                'portfolio_theme' => "TEXT DEFAULT 'default'",
                'social_links' => "TEXT DEFAULT '{}'",
            ],
            'credentials' => [
                'category' => "TEXT DEFAULT 'general'",
                'skills' => "TEXT DEFAULT ''",
                'status' => "TEXT DEFAULT 'active'",
                'created_at' => "DATETIME",
                'credential_type' => "TEXT DEFAULT 'certificate'",
                'credit_hours' => "REAL DEFAULT 0",
                'expiration_date' => "DATETIME",
                'is_uploaded' => "INTEGER DEFAULT 0",
                'renewal_status' => "TEXT DEFAULT ''",
                'pdf_template' => "TEXT DEFAULT 'classic'",
                'department_id' => "INTEGER DEFAULT NULL",
                'skill_codes' => "TEXT DEFAULT ''",
                'otp_claim_enabled' => "INTEGER DEFAULT 0",
                'share_count' => "INTEGER DEFAULT 0",
            ],
            'endorsements' => [
                'endorser_org' => "TEXT DEFAULT ''",
                'endorser_title' => "TEXT DEFAULT ''",
                'signature' => "TEXT DEFAULT ''",
                'status' => "TEXT DEFAULT 'pending'",
            ],
            'bulk_jobs' => [
                'mapping_json' => "TEXT DEFAULT '{}'",
            ],
        ];

        foreach ($migrations as $table => $columns) {
            try {
                $existing = $pdo->query("PRAGMA table_info({$table})")->fetchAll(PDO::FETCH_ASSOC);
                $existingNames = array_column($existing, 'name');
                foreach ($columns as $col => $definition) {
                    if (!in_array($col, $existingNames)) {
                        $pdo->exec("ALTER TABLE {$table} ADD COLUMN {$col} {$definition}");
                    }
                }
            } catch (\Exception $e) {
                error_log("Auto-migrate {$table}.{$col}: " . $e->getMessage());
            }
        }

        // Fix old credential UIDs that contain dots (breaks PHP built-in server)
        try {
            $dotUids = $pdo->query("SELECT id, credential_uid FROM credentials WHERE credential_uid LIKE '%.%'")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($dotUids as $row) {
                $newUid = str_replace('.', '', $row['credential_uid']);
                $pdo->prepare("UPDATE credentials SET credential_uid = ? WHERE id = ?")->execute([$newUid, $row['id']]);
            }
        } catch (\Exception $e) {
            error_log("Fix dot UIDs: " . $e->getMessage());
        }
    }

    /**
     * Seed default plans if they don't exist
     */
    private static function seedPlans(PDO $pdo): void {
        try {
            $count = $pdo->query("SELECT COUNT(*) as c FROM plans")->fetch()['c'];
            if ((int)$count > 0) return;

            $plans = [
                ['free', 'Free', 'user', 0, 0, 5, 1, '{"pdf_download":true,"basic_badge":true}'],
                ['pro', 'Pro', 'user', 9.99, 99, 50, 1, '{"pdf_download":true,"custom_badge":true,"evidence":true,"bulk_upload":false,"priority_support":false}'],
                ['premium', 'Premium', 'user', 19.99, 199, 500, 1, '{"pdf_download":true,"custom_badge":true,"evidence":true,"bulk_upload":true,"priority_support":true,"api_access":true}'],
                ['team', 'Team', 'team', 49.99, 499, 1000, 10, '{"pdf_download":true,"custom_badge":true,"evidence":true,"bulk_upload":true,"priority_support":true,"api_access":true,"team_management":true}'],
                ['institution', 'Institution', 'institution', 199.99, 1999, 99999, 100, '{"pdf_download":true,"custom_badge":true,"evidence":true,"bulk_upload":true,"priority_support":true,"api_access":true,"team_management":true,"white_label":true,"custom_domain":true,"sso":true}'],
            ];

            $stmt = $pdo->prepare("INSERT INTO plans (slug, name, type, price_monthly, price_yearly, max_credentials, max_users, features) VALUES (?,?,?,?,?,?,?,?)");
            foreach ($plans as $p) {
                $stmt->execute($p);
            }
        } catch (\Exception $e) {
            error_log("Seed plans: " . $e->getMessage());
        }
    }

    /**
     * Seed default skill taxonomy entries
     */
    private static function seedSkillTaxonomy(PDO $pdo): void {
        try {
            $count = $pdo->query("SELECT COUNT(*) as c FROM skill_taxonomy")->fetch()['c'];
            if ((int)$count > 0) return;

            $skills = [
                ['PROG-001', 'Programming', 'custom', 'Technology', 'General programming and software development'],
                ['PROG-002', 'Web Development', 'custom', 'Technology', 'Building web applications and sites'],
                ['PROG-003', 'Data Analysis', 'custom', 'Data Science', 'Analyzing and interpreting data'],
                ['PROG-004', 'Machine Learning', 'custom', 'Data Science', 'Building predictive models from data'],
                ['PROG-005', 'Cloud Computing', 'custom', 'Technology', 'Deploying and managing cloud infrastructure'],
                ['BUS-001', 'Project Management', 'custom', 'Business', 'Planning and executing projects'],
                ['BUS-002', 'Leadership', 'custom', 'Business', 'Leading teams and organizations'],
                ['BUS-003', 'Financial Analysis', 'custom', 'Business', 'Understanding financial data and forecasting'],
                ['BUS-004', 'Marketing', 'custom', 'Business', 'Promoting products and services'],
                ['BUS-005', 'Communication', 'custom', 'Soft Skills', 'Effective oral and written communication'],
                ['ESCO-001', 'Problem Solving', 'esco', 'Transversal', 'Identifying and resolving issues systematically'],
                ['ESCO-002', 'Critical Thinking', 'esco', 'Transversal', 'Evaluating information objectively'],
                ['ESCO-003', 'Teamwork', 'esco', 'Transversal', 'Collaborating effectively with others'],
                ['SEC-001', 'Cybersecurity', 'custom', 'Technology', 'Protecting systems and data from threats'],
                ['DES-001', 'Graphic Design', 'custom', 'Design', 'Creating visual content and layouts'],
                ['DES-002', 'UX Design', 'custom', 'Design', 'Designing user-centered digital experiences'],
                ['ENG-001', 'Mechanical Engineering', 'custom', 'Engineering', 'Designing mechanical systems'],
                ['ENG-002', 'Electrical Engineering', 'custom', 'Engineering', 'Electrical systems and circuit design'],
                ['HEALTH-001', 'First Aid', 'custom', 'Healthcare', 'Emergency medical response skills'],
                ['HEALTH-002', 'Patient Care', 'custom', 'Healthcare', 'Providing care for patients'],
            ];

            $stmt = $pdo->prepare("INSERT INTO skill_taxonomy (code, name, framework, category, description) VALUES (?,?,?,?,?)");
            foreach ($skills as $s) {
                $stmt->execute($s);
            }
        } catch (\Exception $e) {
            error_log("Seed skill taxonomy: " . $e->getMessage());
        }
    }

    /**
     * Seed default feature flags
     */
    private static function seedFeatureFlags(PDO $pdo): void {
        try {
            $count = $pdo->query("SELECT COUNT(*) as c FROM feature_flags WHERE institution_id IS NULL")->fetch()['c'];
            if ((int)$count > 0) return;

            $flags = [
                ['api_access', 1, 'Enable REST API access'],
                ['pdf_generation', 1, 'Enable PDF certificate generation'],
                ['bulk_issuance', 1, 'Enable bulk CSV issuance'],
                ['badge_builder', 1, 'Enable visual badge builder'],
                ['webhook_support', 1, 'Enable webhook broadcasting'],
                ['lti_integration', 0, 'Enable LTI 1.3 integration'],
                ['otp_claims', 1, 'Enable OTP-based badge claiming'],
                ['public_portfolios', 1, 'Enable public earner portfolios'],
                ['social_sharing', 1, 'Enable multi-platform social sharing'],
                ['resume_export', 1, 'Enable resume/CV PDF export'],
                ['skill_taxonomy', 1, 'Enable skill taxonomy mapping'],
                ['badge_directory', 1, 'Enable public badge discovery directory'],
                ['evidence_linking', 1, 'Enable evidence linking and attachments'],
                ['endorsements', 1, 'Enable credential endorsements'],
                ['impersonation', 1, 'Enable admin impersonation mode'],
            ];

            $stmt = $pdo->prepare("INSERT INTO feature_flags (institution_id, flag_name, is_enabled, description) VALUES (NULL,?,?,?)");
            foreach ($flags as $f) {
                $stmt->execute($f);
            }
        } catch (\Exception $e) {
            error_log("Seed feature flags: " . $e->getMessage());
        }
    }
}
