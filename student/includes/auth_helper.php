<?php
require_once __DIR__ . '/../../database/db_config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

// PHPAuth v1.x manual requires
require_once __DIR__ . '/../../vendor/phpauth/phpauth/config.class.php';
require_once __DIR__ . '/../../vendor/phpauth/phpauth/auth.class.php';

use PHPAuth\Config as PHPAuthConfig;
use PHPAuth\Auth as PHPAuth;

// Initialize PHPAuth with overrides for students
$auth_config = new PHPAuthConfig($pdo);

// Table overrides
$auth_config->override('table_users', 'students');
$auth_config->override('table_sessions', 'student_sessions');
$auth_config->override('table_attempts', 'student_attempts');
$auth_config->override('table_requests', 'student_requests');

// Cookie overrides to prevent conflict with admin
$auth_config->override('cookie_name', 'student_authID');

// Initialize language
$lang = array();
require_once __DIR__ . '/../../vendor/phpauth/phpauth/languages/en_GB.php';

$auth = new PHPAuth($pdo, $auth_config, $lang);

function is_student_logged_in() {
    global $auth;
    return $auth->isLogged();
}

function get_current_student_data() {
    global $auth, $pdo;
    if (!is_student_logged_in()) return null;
    
    $uid = $auth->getSessionUID($auth->getSessionHash());
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$uid]);
    return $stmt->fetch();
}
?>
