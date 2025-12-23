<?php

use Illuminate\Support\Facades\DB;

$assignments = DB::table('driver_tenant_assignments')->where('driver_id', 10)->get();

if ($assignments->isEmpty()) {
    echo "NO ASSIGNMENTS FOUND FOR DRIVER ID 10!" . PHP_EOL;
} else {
    foreach ($assignments as $assignment) {
        echo "Assignment ID: {$assignment->id} - Driver: {$assignment->driver_id} - Tenant: {$assignment->tenant_id} - User: {$assignment->user_id}" . PHP_EOL;
    }
}

echo PHP_EOL . "Total assignments for driver 10: " . $assignments->count() . PHP_EOL;


