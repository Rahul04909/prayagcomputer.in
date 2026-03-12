<?php
require_once __DIR__ . '/../../database/db_config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPAuth\Config as PHPAuthConfig;
use PHPAuth\Auth as PHPAuth;

$auth_config = new PHPAuthConfig($pdo);
$auth = new PHPAuth($pdo, $auth_config);

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
