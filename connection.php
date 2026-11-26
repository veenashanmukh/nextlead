<?php
// Database settings come from environment variables on the server.
// The fallbacks are your local MAMP settings, so it still works on your computer.
$servername = getenv('DB_HOST') ?: 'localhost';
$username   = getenv('DB_USER') ?: 'root';
$password   = getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'root';
$dbname     = getenv('DB_NAME') ?: 'voting_system';
$port       = (int)(getenv('DB_PORT') ?: 3306);
$useSsl     = filter_var(getenv('DB_SSL') ?: 'false', FILTER_VALIDATE_BOOLEAN);

mysqli_report(MYSQLI_REPORT_OFF);
$conn = mysqli_init();
if ($useSsl) {
    // Cloud MySQL providers (Aiven, TiDB Cloud, etc.) require SSL.
    $conn->ssl_set(null, null, '/etc/ssl/certs/ca-certificates.crt', null, null);
}
$ok = @$conn->real_connect($servername, $username, $password, $dbname, $port, null, $useSsl ? MYSQLI_CLIENT_SSL : 0);

if (!$ok) {
    die("Connection failed: " . mysqli_connect_error());
}
$conn->set_charset('utf8mb4');

// First run on a new database: create the tables automatically from database.sql
$check = $conn->query("SHOW TABLES LIKE 'admins'");
if ($check && $check->num_rows === 0 && is_readable(__DIR__ . '/database.sql')) {
    $schema = preg_replace('/^--.*$/m', '', file_get_contents(__DIR__ . '/database.sql'));
    if ($conn->multi_query($schema)) {
        do { if ($r = $conn->store_result()) { $r->free(); } } while ($conn->more_results() && $conn->next_result());
    }
}
?>
