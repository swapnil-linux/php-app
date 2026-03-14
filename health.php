<?php
/**
 * Health check endpoint for container liveness/readiness probes.
 * Returns JSON with status of the application and database connection.
 *
 * Usage in Kubernetes:
 *   livenessProbe:  GET /health.php
 *   readinessProbe: GET /health.php?db=1  (also checks DB)
 */
header('Content-Type: application/json');

$check_db = isset($_GET['db']);

$payload = [
    'status'    => 'ok',
    'hostname'  => gethostname(),
    'timestamp' => date('c'),
    'php'       => PHP_VERSION,
    'version'   => getenv('APP_VERSION') ?: '1.0',
    'db'        => 'not_checked',
];

if ($check_db) {
    $host   = getenv('DBHOST');
    $user   = getenv('MYSQL_USER');
    $pass   = getenv('MYSQL_PASSWORD');
    $dbname = getenv('MYSQL_DATABASE');

    if ($host && $user) {
        $conn = @new mysqli($host, $user, $pass, $dbname);
        if ($conn->connect_error) {
            $payload['db']     = 'error';
            $payload['status'] = 'degraded';
            http_response_code(503);
        } else {
            $payload['db'] = 'ok';
            $conn->close();
        }
    } else {
        $payload['db'] = 'not_configured';
    }
}

echo json_encode($payload, JSON_PRETTY_PRINT) . "\n";
