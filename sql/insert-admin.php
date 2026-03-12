<?php
require_once __DIR__ . '/../database/db_config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPAuth\Config as PHPAuthConfig;
use PHPAuth\Auth as PHPAuth;

// Create tables if they don't exist
$sql = file_get_contents(__DIR__ . '/../vendor/phpauth/phpauth/database.sql');
$pdo->exec($sql);

// Alter users table to add extra fields
$alterSql = "ALTER TABLE `users` 
    ADD COLUMN `name` varchar(100) DEFAULT NULL,
    ADD COLUMN `mobile` varchar(20) DEFAULT NULL,
    ADD COLUMN `profile_image` varchar(255) DEFAULT NULL AFTER `password`
";
try {
    $pdo->exec($alterSql);
} catch (Exception $e) {
    // Column might already exist
}

$config = new PHPAuthConfig($pdo);
$auth = new PHPAuth($pdo, $config);

$email = "admin@prayagcomputer.in";
$password = "admin123";
$name = "Rahul";
$mobile = "8059982049";
$profile_image = "user-avtar.png";

// Check if admin already exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    $result = $auth->register($email, $password, $password, [], null, false);
    if ($result['error']) {
        echo "Error creating admin: " . $result['message'];
    } else {
        $uid = $result['uid'];
        $stmt = $pdo->prepare("UPDATE users SET name = ?, mobile = ?, profile_image = ?, isactive = 1 WHERE id = ?");
        $stmt->execute([$name, $mobile, $profile_image, $uid]);
        echo "Admin created successfully! Email: $email, Password: $password";
    }
} else {
    echo "Admin already exists.";
}
?>
