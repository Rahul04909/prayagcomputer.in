<?php
require 'database/db_config.php';

try {
    echo "Starting Migration...\n";

    // 1. Add isactive to students
    $pdo->exec("ALTER TABLE students ADD COLUMN isactive TINYINT(1) NOT NULL DEFAULT 1");
    echo "Added isactive to students.\n";

    // 2. Create student_sessions
    $pdo->exec("CREATE TABLE IF NOT EXISTS `student_sessions` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `uid` int(11) NOT NULL,
      `hash` varchar(40) NOT NULL,
      `expiredate` datetime NOT NULL,
      `ip` varchar(39) NOT NULL,
      `agent` varchar(200) NOT NULL,
      `cookie_crc` varchar(40) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
    echo "Created student_sessions table.\n";

    // 3. Create student_attempts
    $pdo->exec("CREATE TABLE IF NOT EXISTS `student_attempts` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `ip` varchar(39) NOT NULL,
      `expiredate` datetime NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
    echo "Created student_attempts table.\n";

    // 4. Create student_requests (for future use like password reset)
    $pdo->exec("CREATE TABLE IF NOT EXISTS `student_requests` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `uid` int(11) NOT NULL,
      `rkey` varchar(20) NOT NULL,
      `expire` datetime NOT NULL,
      `type` varchar(20) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
    echo "Created student_requests table.\n";

    echo "Migration Completed Successfully!";
} catch (PDOException $e) {
    echo "Migration Failed: " . $e->getMessage();
}
?>
