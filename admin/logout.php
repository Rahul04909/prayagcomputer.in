<?php
require_once __DIR__ . '/includes/auth_helper.php';

if (is_logged_in()) {
    $auth->logout($auth->getSessionHash());
}

header("Location: login.php");
exit();
?>
