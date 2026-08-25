<?php
session_start();

// Database credentials. Copy this file to config.php and fill in real values.
// On Hostinger: hPanel > Databases > MySQL Databases (host is usually "localhost").
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'djerez_admin');
define('DB_USER', 'root');
define('DB_PASS', '');

function getDb(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }
    return $pdo;
}

define('IMAGES_DIR', __DIR__ . '/../Images/gallery/');
define('VIDEOS_DIR', __DIR__ . '/../Videos/');

define('MAX_IMAGES', 30);
define('MAX_VIDEOS', 5);
define('MAX_IMAGE_SIZE', 3 * 1024 * 1024);  // 3MB
define('MAX_VIDEO_SIZE', 50 * 1024 * 1024); // 50MB

if (!is_dir(IMAGES_DIR)) {
    mkdir(IMAGES_DIR, 0755, true);
}
if (!is_dir(VIDEOS_DIR)) {
    mkdir(VIDEOS_DIR, 0755, true);
}
