<?php
// Simple healthcheck endpoint - does not require full Yii2 bootstrap
http_response_code(200);
header('Content-Type: application/json');
echo json_encode(['status' => 'ok', 'timestamp' => time()]);
