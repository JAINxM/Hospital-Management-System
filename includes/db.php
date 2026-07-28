<?php
ob_start();
// ============================================================
// DATABASE CONNECTION (PDO)
// Host: localhost | User: root | Pass: (blank) | DB: hotel_management
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '2216');
define('DB_NAME', 'hotel_management');
define('DB_CHARSET', 'utf8mb4');

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // If database doesn't exist, try connecting without DB name to inform user
    try {
        $pdoTest = new PDO("mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
        die("<div style='font-family: sans-serif; padding: 2rem; background: #fee2e2; border: 1px solid #ef4444; color: #991b1b; border-radius: 8px; max-width: 600px; margin: 3rem auto;'>
            <h2>Database Connection Error</h2>
            <p>Database <strong>" . DB_NAME . "</strong> does not exist.</p>
            <p>Please open <strong>phpMyAdmin</strong> and import the SQL file located at: <code>database/hotel.sql</code></p>
        </div>");
    } catch (PDOException $ex) {
        die("<div style='font-family: sans-serif; padding: 2rem; background: #fee2e2; border: 1px solid #ef4444; color: #991b1b; border-radius: 8px; max-width: 600px; margin: 3rem auto;'>
            <h2>Database Connection Error</h2>
            <p>Could not connect to MySQL server. Please ensure XAMPP MySQL service is running.</p>
            <small>Error: " . htmlspecialchars($ex->getMessage()) . "</small>
        </div>");
    }
}
