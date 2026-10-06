<?php
require_once __DIR__ . '/includes/auth_helper.php';
$auth->logout($auth->getSessionHash());
setcookie($auth_config->cookie_name, '', time() - 3600, $auth_config->cookie_path, $auth_config->cookie_domain, $auth_config->cookie_secure, $auth_config->cookie_http);
header("Location: login.php");
exit();
?>
