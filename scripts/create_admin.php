<?php
// Create a test admin user if it does not exist
require __DIR__ . '/../app/core/config.php';

try {
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email');
    $email = 'admin@local';
    $stmt->execute([':email' => $email]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        echo "Admin already exists (id={$row['id']}).\n";
        exit(0);
    }

    $username = 'admin';
    $password = password_hash('Admin@123', PASSWORD_DEFAULT);
    $role = 'admin';

    $insert = $pdo->prepare('INSERT INTO users (username, email, password_hash, role) VALUES (:username, :email, :password_hash, :role)');
    $insert->execute([
        ':username' => $username,
        ':email' => $email,
        ':password_hash' => $password,
        ':role' => $role
    ]);

    echo "Admin created: {$username} / {$email} (password: Admin@123)\n";
} catch (Exception $e) {
    echo "Error creating admin: " . $e->getMessage() . "\n";
    exit(1);
}
