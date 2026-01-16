<?php
// Super simple test - no Yii2, no dependencies
header('Content-Type: text/html');
echo "<h1>Apache PHP Test</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Time: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>Server Port: " . ($_SERVER['SERVER_PORT'] ?? 'unknown') . "</p>";
echo "<p>Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'unknown') . "</p>";
phpinfo(INFO_MODULES);
