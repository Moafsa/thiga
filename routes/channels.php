<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Driver location tracking channels
Broadcast::channel('tenant.{tenantId}.driver.{driverId}', function ($user, $tenantId, $driverId) {
    // Driver can listen to their own location updates
    // Admin can listen to any driver in their tenant
    return (int) $user->tenant_id === (int) $tenantId && (
        (int) $user->id === (int) $driverId ||
        $user->hasRole('admin') ||
        $user->hasRole('manager')
    );
});

// Route tracking channel
Broadcast::channel('tenant.{tenantId}.route.{routeId}', function ($user, $tenantId, $routeId) {
    // Users in the same tenant can listen to route updates
    return (int) $user->tenant_id === (int) $tenantId;
});

// Admin dashboard channel (all drivers in tenant)
Broadcast::channel('tenant.{tenantId}.admin.drivers', function ($user, $tenantId) {
    // Only admins and managers can listen to all drivers
    return (int) $user->tenant_id === (int) $tenantId && (
        $user->hasRole('admin') ||
        $user->hasRole('manager')
    );
});
