<?php
$url = $argv[1] ?? 'http://127.0.0.1:8000/api/v1/auth/csrf';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
curl_close($ch);
echo "HTTP_CODE=$httpCode\n";
echo $response . PHP_EOL;
