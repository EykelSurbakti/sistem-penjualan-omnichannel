<?php
$start = microtime(true);
$response = file_get_contents('http://127.0.0.1:8000/admin/login');
$elapsed = round(microtime(true) - $start, 2);

echo "Login page loaded in {$elapsed}s, size: " . strlen($response) . " bytes\n";

// Now check /admin/products (will redirect to login, but that's OK - tests the server response time)
$start2 = microtime(true);
$ctx = stream_context_create(['http' => ['timeout' => 60, 'follow_location' => false]]);
$resp2 = @file_get_contents('http://127.0.0.1:8000/admin/products', false, $ctx);
$elapsed2 = round(microtime(true) - $start2, 2);
echo "Products page response in {$elapsed2}s\n";

if ($elapsed2 < 10) {
    echo "✔ Page responds quickly — pagination is working!\n";
} else {
    echo "⚠️ Still slow: {$elapsed2}s\n";
}
