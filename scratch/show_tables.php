<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
$tables = DB::select('SHOW TABLES');
foreach ($tables as $table) {
    $values = (array)$table;
    echo reset($values) . "\n";
}
