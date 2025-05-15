<?php
define('DB_HOST', 'db');
define('DB_USER', 'root');
define('DB_PASS', 'rootpassword');
define('DB_NAME', 'auth_system');
define('DB_CHARSET', 'utf8mb4');

try {
    $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ];
    
    $conn = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>