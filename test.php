<?php
$hostname    = htmlspecialchars(gethostname());
$server_ip   = htmlspecialchars($_SERVER['SERVER_ADDR'] ?? 'unknown');
$app_version = getenv('APP_VERSION') ?: '1.0';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pod Info</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 30px; background: #f0f2f5; }
        .card { background: white; padding: 24px; border-radius: 8px; max-width: 500px; box-shadow: 0 2px 6px rgba(0,0,0,.1); }
        h1 { margin-top: 0; }
        table { border-collapse: collapse; width: 100%; }
        td { padding: 8px 12px; border-bottom: 1px solid #eee; }
        td:first-child { color: #888; font-size: .85em; text-transform: uppercase; width: 40%; }
        td:last-child { font-weight: bold; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Pod / Container Info</h1>
        <table>
            <tr><td>Hostname</td><td><?= $hostname ?></td></tr>
            <tr><td>IP Address</td><td><?= $server_ip ?></td></tr>
            <tr><td>App Version</td><td><?= htmlspecialchars($app_version) ?></td></tr>
            <tr><td>PHP Version</td><td><?= htmlspecialchars(PHP_VERSION) ?></td></tr>
        </table>
    </div>
</body>
</html>
