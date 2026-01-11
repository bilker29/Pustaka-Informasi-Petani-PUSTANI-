<?php

/**
 * config/koneksi.php
 * Database connection with error handling and sensible defaults.
 *
 * Usage: include or require this file and use $koneksi (mysqli object).
 */

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Read from environment if available (safer for production)
$dbHost = getenv('DB_HOST') ?: '127.0.0.1'; // use 127.0.0.1 to force TCP (avoids socket issues)
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: '';
$dbName = getenv('DB_NAME') ?: 'pustani_db';
$dbPort = getenv('DB_PORT') !== false ? (int)getenv('DB_PORT') : 3306;
// If you need to connect via a specific socket, set DB_SOCKET or edit here.
// $dbSocket = getenv('DB_SOCKET') ?: '/var/run/mysqld/mysqld.sock';

try {
    // If you prefer socket-based connection, use the last parameter:
    // $koneksi = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName, 0, $dbSocket);
    $koneksi = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
    $koneksi->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    // Log the technical error for admins, but show a generic message to users.
    error_log('Database connection error: ' . $e->getMessage());
    // Optionally: error_log($e->getTraceAsString());
    if (php_sapi_name() === 'cli') {
        // Helpful when running command-line scripts
        echo "DB connection error: " . $e->getMessage() . PHP_EOL;
    } else {
        http_response_code(500);
        echo "Database connection error. Please contact the administrator.";
    }
    exit;
}
