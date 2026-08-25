-- Run this against the MySQL database created in Hostinger hPanel
-- (Databases > MySQL Databases). Then insert your own admin user --
-- generate a password hash with: php -r "echo password_hash('yourpassword', PASSWORD_DEFAULT);"

CREATE TABLE IF NOT EXISTS admin_users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- INSERT INTO admin_users (username, password_hash) VALUES ('admin', '<paste generated hash here>');
