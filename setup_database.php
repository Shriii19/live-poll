<?php
/**
 * Database Setup Script
 * Run this to create the database tables and seed initial data
 */

$config = [
    'host' => '127.0.0.1',
    'port' => 3306,
    'database' => 'live_poll',
    'username' => 'root',
    'password' => ''
];

echo "=== Live Poll Database Setup ===\n\n";

try {
    // First connect without database to create it
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']}",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['database']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Database '{$config['database']}' created/verified\n";
    
    // Connect to the database
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']}",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Create users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            is_admin TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Users table created\n";
    
    // Create polls table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS polls (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            question VARCHAR(255) NOT NULL,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Polls table created\n";
    
    // Create poll_options table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS poll_options (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            poll_id BIGINT UNSIGNED NOT NULL,
            option_text VARCHAR(255) NOT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            FOREIGN KEY (poll_id) REFERENCES polls(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Poll options table created\n";
    
    // Create votes table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS votes (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            poll_id BIGINT UNSIGNED NOT NULL,
            option_id BIGINT UNSIGNED NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            is_released TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            FOREIGN KEY (poll_id) REFERENCES polls(id) ON DELETE CASCADE,
            FOREIGN KEY (option_id) REFERENCES poll_options(id) ON DELETE CASCADE,
            INDEX idx_poll_ip (poll_id, ip_address)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Votes table created\n";
    
    // Create vote_history table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS vote_history (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            poll_id BIGINT UNSIGNED NOT NULL,
            option_id BIGINT UNSIGNED NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            action ENUM('voted', 'released') NOT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            FOREIGN KEY (poll_id) REFERENCES polls(id) ON DELETE CASCADE,
            INDEX idx_poll_ip_history (poll_id, ip_address)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Vote history table created\n";
    
    // Create sessions table (for Laravel sessions)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS sessions (
            id VARCHAR(255) PRIMARY KEY,
            user_id BIGINT UNSIGNED NULL,
            ip_address VARCHAR(45) NULL,
            user_agent TEXT NULL,
            payload LONGTEXT NOT NULL,
            last_activity INT NOT NULL,
            INDEX idx_last_activity (last_activity)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Sessions table created\n";
    
    // Seed demo users
    $adminPassword = password_hash('password', PASSWORD_BCRYPT);
    $userPassword = password_hash('password', PASSWORD_BCRYPT);
    
    // Check if users exist
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("
            INSERT INTO users (name, email, password, is_admin, created_at, updated_at) VALUES
            ('Admin User', 'admin@poll.com', '{$adminPassword}', 1, NOW(), NOW()),
            ('Regular User', 'user@poll.com', '{$userPassword}', 0, NOW(), NOW())
        ");
        echo "✓ Demo users created\n";
        echo "  - admin@poll.com / password (Admin)\n";
        echo "  - user@poll.com / password (User)\n";
    }
    
    // Seed demo polls
    $stmt = $pdo->query("SELECT COUNT(*) FROM polls");
    if ($stmt->fetchColumn() == 0) {
        // Poll 1
        $pdo->exec("INSERT INTO polls (question, status, created_at, updated_at) VALUES ('What is your favorite programming language?', 'active', NOW(), NOW())");
        $pollId1 = $pdo->lastInsertId();
        $pdo->exec("INSERT INTO poll_options (poll_id, option_text, created_at, updated_at) VALUES 
            ({$pollId1}, 'PHP', NOW(), NOW()),
            ({$pollId1}, 'JavaScript', NOW(), NOW()),
            ({$pollId1}, 'Python', NOW(), NOW()),
            ({$pollId1}, 'Java', NOW(), NOW())
        ");
        
        // Poll 2
        $pdo->exec("INSERT INTO polls (question, status, created_at, updated_at) VALUES ('Best web framework?', 'active', NOW(), NOW())");
        $pollId2 = $pdo->lastInsertId();
        $pdo->exec("INSERT INTO poll_options (poll_id, option_text, created_at, updated_at) VALUES 
            ({$pollId2}, 'Laravel', NOW(), NOW()),
            ({$pollId2}, 'React', NOW(), NOW()),
            ({$pollId2}, 'Vue.js', NOW(), NOW()),
            ({$pollId2}, 'Angular', NOW(), NOW())
        ");
        
        // Poll 3
        $pdo->exec("INSERT INTO polls (question, status, created_at, updated_at) VALUES ('Preferred database?', 'active', NOW(), NOW())");
        $pollId3 = $pdo->lastInsertId();
        $pdo->exec("INSERT INTO poll_options (poll_id, option_text, created_at, updated_at) VALUES 
            ({$pollId3}, 'MySQL', NOW(), NOW()),
            ({$pollId3}, 'PostgreSQL', NOW(), NOW()),
            ({$pollId3}, 'MongoDB', NOW(), NOW()),
            ({$pollId3}, 'SQLite', NOW(), NOW())
        ");
        
        echo "✓ Demo polls created\n";
    }
    
    echo "\n=== Setup Complete! ===\n";
    echo "Now run: php -S localhost:8000 -t public\n";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
