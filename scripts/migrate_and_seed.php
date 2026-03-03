<?php
/**
 * CertiMe - Database Migration & Admin Seed
 * Adds all missing columns and seeds an admin account
 */
require __DIR__ . '/../app/core/config.php';
require __DIR__ . '/../app/core/Database.php';
Database::setup();
$pdo = Database::getInstance();

echo "=== CertiMe DB Migration ===\n\n";

// Helper: add column if not exists
function addColumnIfMissing(PDO $pdo, string $table, string $column, string $type, string $default = ''): void {
    $cols = $pdo->query("PRAGMA table_info({$table})")->fetchAll(PDO::FETCH_ASSOC);
    $names = array_column($cols, 'name');
    if (!in_array($column, $names)) {
        $sql = "ALTER TABLE {$table} ADD COLUMN {$column} {$type}";
        if ($default !== '') $sql .= " DEFAULT {$default}";
        $pdo->exec($sql);
        echo "  + Added {$table}.{$column}\n";
    } else {
        echo "  . {$table}.{$column} exists\n";
    }
}

// ===== USERS TABLE =====
echo "Users table:\n";
addColumnIfMissing($pdo, 'users', 'full_name', 'TEXT', "''");
addColumnIfMissing($pdo, 'users', 'bio', 'TEXT', "''");
addColumnIfMissing($pdo, 'users', 'avatar_url', 'TEXT', "''");
addColumnIfMissing($pdo, 'users', 'is_active', 'INTEGER', '1');
addColumnIfMissing($pdo, 'users', 'last_login', 'DATETIME');
addColumnIfMissing($pdo, 'users', 'updated_at', 'DATETIME');

// ===== CREDENTIALS TABLE =====
echo "\nCredentials table:\n";
addColumnIfMissing($pdo, 'credentials', 'category', 'TEXT', "'general'");
addColumnIfMissing($pdo, 'credentials', 'skills', 'TEXT', "''");
addColumnIfMissing($pdo, 'credentials', 'status', 'TEXT', "'active'");
addColumnIfMissing($pdo, 'credentials', 'created_at', 'DATETIME');
addColumnIfMissing($pdo, 'credentials', 'credit_hours', 'REAL', '0');
addColumnIfMissing($pdo, 'credentials', 'credential_type', 'TEXT', "'certificate'");

// ===== ENDORSEMENTS TABLE =====
echo "\nEndorsements table:\n";
addColumnIfMissing($pdo, 'endorsements', 'endorser_org', 'TEXT', "''");
addColumnIfMissing($pdo, 'endorsements', 'endorser_title', 'TEXT', "''");
addColumnIfMissing($pdo, 'endorsements', 'signature', 'TEXT', "''");
addColumnIfMissing($pdo, 'endorsements', 'status', 'TEXT', "'pending'");

// ===== INDEXES =====
echo "\nIndexes:\n";
$indexes = [
    "CREATE INDEX IF NOT EXISTS idx_credentials_user ON credentials(user_id)",
    "CREATE INDEX IF NOT EXISTS idx_credentials_uid ON credentials(credential_uid)",
    "CREATE INDEX IF NOT EXISTS idx_credentials_status ON credentials(status)",
    "CREATE INDEX IF NOT EXISTS idx_endorsements_credential ON endorsements(credential_id)",
    "CREATE INDEX IF NOT EXISTS idx_audit_user ON audit_log(user_id)",
    "CREATE INDEX IF NOT EXISTS idx_audit_timestamp ON audit_log(timestamp)",
];
foreach ($indexes as $idx) {
    try {
        $pdo->exec($idx);
        echo "  + OK: " . substr($idx, 0, 60) . "...\n";
    } catch (Exception $e) {
        echo "  ! " . $e->getMessage() . "\n";
    }
}

// ===== SEED ADMIN ACCOUNT =====
echo "\n=== Seeding Admin Account ===\n";
$adminUsername = 'admin';
$adminEmail = 'admin@certime.local'; 
$adminPassword = 'Admin123!';

$existing = $pdo->prepare("SELECT id, username, email, role FROM users WHERE username = :u OR email = :e");
$existing->execute([':u' => $adminUsername, ':e' => $adminEmail]);
$existingUser = $existing->fetch(PDO::FETCH_ASSOC);

if ($existingUser) {
    // Update to admin role and reset password
    $hash = password_hash($adminPassword, PASSWORD_ARGON2ID);
    $stmt = $pdo->prepare("UPDATE users SET password_hash = :hash, role = 'admin', email = :email, is_active = 1 WHERE username = :u");
    $stmt->execute([':hash' => $hash, ':email' => $adminEmail, ':u' => $adminUsername]);
    echo "  Updated existing user '{$existingUser['username']}' (id={$existingUser['id']}) → admin role, password reset\n";
} else {
    $hash = password_hash($adminPassword, PASSWORD_ARGON2ID);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role, full_name, is_active) VALUES (:u, :e, :h, 'admin', 'Platform Admin', 1)");
    $stmt->execute([':u' => $adminUsername, ':e' => $adminEmail, ':h' => $hash]);
    echo "  Created admin user (id=" . $pdo->lastInsertId() . ")\n";
}

echo "\n╔══════════════════════════════════════╗\n";
echo "║  Admin Credentials:                  ║\n";
echo "║  Username: admin                     ║\n";
echo "║  Password: Admin123!                 ║\n";
echo "╚══════════════════════════════════════╝\n";

// ===== Seed a sample credential if none exist =====
$credCount = (int)$pdo->query("SELECT COUNT(*) as c FROM credentials")->fetch(PDO::FETCH_ASSOC)['c'];
echo "\nExisting credentials: {$credCount}\n";

// Verify final schema
echo "\n=== Final Schema Verification ===\n";
foreach (['users', 'credentials', 'endorsements'] as $table) {
    $cols = $pdo->query("PRAGMA table_info({$table})")->fetchAll(PDO::FETCH_ASSOC);
    echo "{$table}: " . implode(', ', array_column($cols, 'name')) . "\n";
}

echo "\nMigration complete!\n";
