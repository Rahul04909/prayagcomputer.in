<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../database/db_config.php';
require_once __DIR__ . '/../vendor/autoload.php';

// PHPAuth v1.x manual requires as it doesn't follow PSR-4 naming
require_once __DIR__ . '/../vendor/phpauth/phpauth/config.class.php';
require_once __DIR__ . '/../vendor/phpauth/phpauth/auth.class.php';

use PHPAuth\Config as PHPAuthConfig;
use PHPAuth\Auth as PHPAuth;

// Verify SQL file existence
$sqlPath = __DIR__ . '/../vendor/phpauth/phpauth/database.sql';
if (!file_exists($sqlPath)) {
    die("Error: SQL file not found at $sqlPath. Please ensure PHPAuth is correctly installed in vendor.");
}

// Create tables if they don't exist
$sql = file_get_contents($sqlPath);
try {
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
} catch (PDOException $e) {
    echo "Warning during table creation: " . $e->getMessage() . "<br>";
}

// Initialize PHPAuth
$config = new PHPAuthConfig($pdo);

// Initialize language (Required by PHPAuth v1)
$lang = array();
require_once __DIR__ . '/../vendor/phpauth/phpauth/languages/en_GB.php';

$auth = new PHPAuth($pdo, $config, $lang);

// Alter users table to add extra fields
$alterSql = "ALTER TABLE `users` 
    ADD COLUMN `name` varchar(100) DEFAULT NULL,
    ADD COLUMN `mobile` varchar(20) DEFAULT NULL,
    ADD COLUMN `profile_image` varchar(255) DEFAULT NULL AFTER `password`
";
try {
    // Only alter if the columns don't exist
    $pdo->exec($alterSql);
} catch (Exception $e) {
    // Column might already exist, ignore this error
}

$email = "admin@prayagcomputer.in";
$password = "Admin@123";
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
        $uid = $auth->getUID($email);
        $stmt = $pdo->prepare("UPDATE users SET name = ?, mobile = ?, profile_image = ?, isactive = 1 WHERE id = ?");
        $stmt->execute([$name, $mobile, $profile_image, $uid]);
        echo "Admin created successfully! Email: $email, Password: $password";
    }
} else {
    echo "Admin already exists.";
}
?>
