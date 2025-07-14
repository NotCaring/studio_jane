<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'studio_jane');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8');

// Create database connection
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Site configuration
define('SITE_NAME', 'Studio Jane');
define('SITE_EMAIL', 'info@studiojane.com');
define('SITE_PHONE', '+57 300 123 4567');
define('SITE_WHATSAPP', '573001234567');
define('SITE_ADDRESS', 'Calle 123 #45-67, Bogotá');

// Email configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-app-password');

// Security
define('HASH_ALGO', 'sha256');
define('SESSION_TIMEOUT', 7200); // 2 hours
?>