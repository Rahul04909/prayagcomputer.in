<?php
require_once __DIR__ . '/../../database/db_config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

// PHPAuth v1.x manual requires as it doesn't follow PSR-4 naming
require_once __DIR__ . '/../../vendor/phpauth/phpauth/config.class.php';
require_once __DIR__ . '/../../vendor/phpauth/phpauth/auth.class.php';

use PHPAuth\Config as PHPAuthConfig;
use PHPAuth\Auth as PHPAuth;

// Initialize PHPAuth
$auth_config = new PHPAuthConfig($pdo);

// Initialize language (Required by PHPAuth v1)
$lang = array();
require_once __DIR__ . '/../../vendor/phpauth/phpauth/languages/en_GB.php';

$auth = new PHPAuth($pdo, $auth_config, $lang);

function is_logged_in() {
    global $auth;
    return $auth->isLogged();
}

function get_current_user_data() {
    global $auth, $pdo;
    if (!is_logged_in()) return null;
    
    $uid = $auth->getCurrentUID();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$uid]);
    return $stmt->fetch();
}
?>
