<?php
/**
 * THE ROMA PALACE — Database Connection & Auto-Initialization Engine
 * BTech CSE DBMS Mini Project
 * 
 * Supports:
 * 1. Primary: MySQL 8.0+ / MariaDB via PDO (if running on 127.0.0.1:3306)
 * 2. Secondary: Zero-Config SQLite fallback (pre-compiled in database/roma_palace.sqlite)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Credentials
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'roma_palace');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');

$pdo = null;
$db_driver = 'sqlite'; // default to rock-solid zero-config sqlite

// 1. Try MySQL only if explicitly available and responsive
if (getenv('USE_MYSQL') === 'true') {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => 2
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        $testStmt = $pdo->query("SELECT 1 FROM hotels LIMIT 1");
        $db_driver = 'mysql';
    } catch (Exception $e) {
        $pdo = setup_sqlite_fallback();
        $db_driver = 'sqlite';
    }
} else {
    // Check if MySQL with populated database is running
    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => 1
        ];
        $testPdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        $testStmt = $testPdo->query("SELECT 1 FROM hotels LIMIT 1");
        $pdo = $testPdo;
        $db_driver = 'mysql';
    } catch (Exception $e) {
        $pdo = setup_sqlite_fallback();
        $db_driver = 'sqlite';
    }
}

define('CURRENT_DB_DRIVER', $db_driver);

/**
 * Setup SQLite database if MySQL server is offline or table init fails
 */
function setup_sqlite_fallback() {
    $dbDir = __DIR__ . '/../database';
    if (!is_dir($dbDir)) {
        mkdir($dbDir, 0777, true);
    }
    $sqliteFile = $dbDir . '/roma_palace.sqlite';

    $sqlitePdo = new PDO("sqlite:" . $sqliteFile, null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Verify if tables exist; if not, rebuild from SQL
    try {
        $sqlitePdo->query("SELECT 1 FROM hotels LIMIT 1");
    } catch (Exception $ex) {
        rebuild_sqlite_database($sqlitePdo, $sqliteFile);
    }

    return $sqlitePdo;
}

/**
 * Rebuild SQLite schema and seed data cleanly from SQL file
 */
function rebuild_sqlite_database($pdo, $dbPath) {
    $sqlFile = __DIR__ . '/../database/roma_palace.sql';
    if (!file_exists($sqlFile)) return;

    $rawSql = file_get_contents($sqlFile);
    $lines = explode("\n", $rawSql);
    $filteredLines = [];

    foreach ($lines as $line) {
        $trimmed = trim($line);
        if (empty($trimmed) || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*')) {
            continue;
        }
        if (stripos($trimmed, 'CREATE DATABASE') !== false || stripos($trimmed, 'USE `') !== false || stripos($trimmed, 'SET FOREIGN_KEY_CHECKS') !== false) {
            continue;
        }
        $filteredLines[] = $line;
    }

    $cleanSql = implode("\n", $filteredLines);
    $cleanSql = preg_replace('/`/', '', $cleanSql);
    $cleanSql = preg_replace('/INT\s+AUTO_INCREMENT\s+PRIMARY\s+KEY/i', 'INTEGER PRIMARY KEY AUTOINCREMENT', $cleanSql);
    $cleanSql = preg_replace('/AUTO_INCREMENT/i', 'AUTOINCREMENT', $cleanSql);
    $cleanSql = preg_replace('/ENUM\([^)]+\)/i', 'TEXT', $cleanSql);
    $cleanSql = preg_replace('/DECIMAL\([^)]+\)/i', 'REAL', $cleanSql);
    $cleanSql = preg_replace('/ON\s+UPDATE\s+CURRENT_TIMESTAMP/i', '', $cleanSql);
    $cleanSql = preg_replace('/,\s*INDEX\s+[a-zA-Z0-9_]+\s*\([^)]+\)/i', '', $cleanSql);
    $cleanSql = preg_replace('/,\s*UNIQUE\s+KEY\s+[a-zA-Z0-9_]+\s*\([^)]+\)/i', '', $cleanSql);
    $cleanSql = preg_replace('/,\s*KEY\s+[a-zA-Z0-9_]+\s*\([^)]+\)/i', '', $cleanSql);
    $cleanSql = preg_replace('/\)\s*ENGINE\s*=\s*InnoDB[^;]*;/i', ');', $cleanSql);

    $statements = explode(';', $cleanSql);
    foreach ($statements as $stmt) {
        $s = trim($stmt);
        if (!empty($s)) {
            try {
                $pdo->exec($s);
            } catch (Exception $e) {
                // ignore
            }
        }
    }
}

/**
 * Universal Database Helper Functions
 */
function db_query($query, $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt;
}

function db_fetch_all($query, $params = []) {
    $stmt = db_query($query, $params);
    return $stmt->fetchAll();
}

function db_fetch_one($query, $params = []) {
    $stmt = db_query($query, $params);
    return $stmt->fetch() ?: null;
}

function db_execute($query, $params = []) {
    $stmt = db_query($query, $params);
    return $stmt->rowCount();
}

function db_insert_id() {
    global $pdo;
    return $pdo->lastInsertId();
}

/**
 * Currency Formatter for Indian Rupees (INR)
 */
function format_inr($amount) {
    return '₹' . number_format((float)$amount, 0, '.', ',');
}

/**
 * Stay Date Formatter
 */
function format_stay_date($dateStr) {
    if (!$dateStr) return '';
    return date('d M Y', strtotime($dateStr));
}
