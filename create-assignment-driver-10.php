<?php

use App\Models\Driver;
use App\Models\DriverTenantAssignment;
use Illuminate\Support\Facades\DB;

$driver = Driver::find(10);

if (!$driver) {
    echo "Driver 10 not found!" . PHP_EOL;
    exit;
}

echo "Driver found: ID={$driver->id}, Name={$driver->name}, User ID={$driver->user_id}, Tenant ID={$driver->tenant_id}" . PHP_EOL;

// Check if assignment already exists
$existingAssignment = DriverTenantAssignment::where('driver_id', $driver->id)->first();

if ($existingAssignment) {
    echo "Assignment already exists: ID={$existingAssignment->id}" . PHP_EOL;
} else {
    echo "Creating new assignment..." . PHP_EOL;
    
    $assignment = DriverTenantAssignment::create([
        'driver_id' => $driver->id,
        'tenant_id' => $driver->tenant_id,
        'user_id' => $driver->user_id,
    ]);
    
    echo "Assignment created successfully! ID={$assignment->id}" . PHP_EOL;
}


