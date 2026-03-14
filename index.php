<?php
$servername = getenv('DBHOST');
$username   = getenv('MYSQL_USER');
$password   = getenv('MYSQL_PASSWORD');
$dbname     = getenv('MYSQL_DATABASE');

$app_version = getenv('APP_VERSION') ?: '1.0';
$app_color   = getenv('APP_COLOR')   ?: '#3c6eb4';

$db_error = null;
$friends  = [];

if ($servername && $username) {
    $conn = @new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        $db_error = "Could not connect to database. Check DBHOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE.";
        error_log("DB connection failed: " . $conn->connect_error);
    } else {
        $result = $conn->query("SELECT id, firstname, lastname FROM MyGuests ORDER BY id");
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $friends[] = $row;
            }
        }
        $conn->close();
    }
} else {
    $db_error = "Database not configured. Set DBHOST, MYSQL_USER, MYSQL_PASSWORD, and MYSQL_DATABASE environment variables.";
}

$hostname  = htmlspecialchars(gethostname());
$server_ip = htmlspecialchars($_SERVER['SERVER_ADDR'] ?? 'unknown');
$php_ver   = PHP_VERSION;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friends App v<?= htmlspecialchars($app_version) ?></title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f0f2f5; }
        .header {
            background: <?= htmlspecialchars($app_color) ?>;
            color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px;
        }
        .header h1 { margin: 10px 0 0; }
        .header img { border-radius: 4px; max-width: 100%; }
        .card {
            background: white; padding: 20px; border-radius: 8px;
            margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
        .card h2 { margin-top: 0; color: #333; border-bottom: 2px solid #eee; padding-bottom: 8px; }
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; }
        .info-item { background: #f8f9fa; padding: 12px; border-radius: 6px; }
        .info-label { font-size: 0.75em; color: #888; text-transform: uppercase; letter-spacing: .05em; }
        .info-value { font-weight: bold; font-size: 1.05em; word-break: break-all; margin-top: 4px; }
        .error { background: #fff0f0; border: 1px solid #f5a5a5; padding: 15px; border-radius: 6px; color: #c00; }
        .friend-list { list-style: none; padding: 0; margin: 0; }
        .friend-list li { padding: 10px 12px; border-bottom: 1px solid #f0f0f0; }
        .friend-list li:last-child { border-bottom: none; }
        .friend-id { color: #aaa; font-size: 0.85em; margin-right: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <img width="420" src="friends.jpg" alt="Friends">
        <h1>Friends App &nbsp;<small style="font-size:.55em;opacity:.85">v<?= htmlspecialchars($app_version) ?></small></h1>
    </div>

    <div class="card">
        <h2>Container / Pod Info</h2>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Hostname</div>
                <div class="info-value"><?= $hostname ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Server IP</div>
                <div class="info-value"><?= $server_ip ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">App Version</div>
                <div class="info-value"><?= htmlspecialchars($app_version) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">PHP Version</div>
                <div class="info-value"><?= htmlspecialchars($php_ver) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Database Host</div>
                <div class="info-value"><?= htmlspecialchars($servername ?: 'not set') ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Database Name</div>
                <div class="info-value"><?= htmlspecialchars($dbname ?: 'not set') ?></div>
            </div>
        </div>
    </div>

    <div class="card">
        <h2>Friends from Database</h2>
        <?php if ($db_error): ?>
            <div class="error"><?= htmlspecialchars($db_error) ?></div>
        <?php elseif (empty($friends)): ?>
            <p style="color:#888">No records found. Did you run <code>init.sql</code>?</p>
        <?php else: ?>
            <ul class="friend-list">
                <?php foreach ($friends as $f): ?>
                    <li>
                        <span class="friend-id">#<?= (int)$f['id'] ?></span>
                        <?= htmlspecialchars($f['firstname']) ?> <?= htmlspecialchars($f['lastname']) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</body>
</html>
