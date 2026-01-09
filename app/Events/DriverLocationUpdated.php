<?php

namespace App\Events;

use App\Models\Driver;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DriverLocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Driver $driver;
    public float $latitude;
    public float $longitude;
    public ?int $routeId;
    public ?int $shipmentId;
    public ?float $speed;
    public ?float $heading;
    public bool $isMoving;

    /**
     * Create a new event instance.
     */
    public function __construct(
        Driver $driver,
        float $latitude,
        float $longitude,
        ?int $routeId = null,
        ?int $shipmentId = null,
        ?float $speed = null,
        ?float $heading = null,
        bool $isMoving = false
    ) {
        $this->driver = $driver;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->routeId = $routeId;
        $this->shipmentId = $shipmentId;
        $this->speed = $speed;
        $this->heading = $heading;
        $this->isMoving = $isMoving;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        // Broadcast to tenant-specific channel
        $channels = [
            new PrivateChannel("tenant.{$this->driver->tenant_id}.driver.{$this->driver->id}"),
        ];

        // If driver has active route, broadcast to route channel
        if ($this->routeId) {
            $channels[] = new PrivateChannel("tenant.{$this->driver->tenant_id}.route.{$this->routeId}");
        }

        // Broadcast to admin dashboard
        $channels[] = new PrivateChannel("tenant.{$this->driver->tenant_id}.admin.drivers");

        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'driver.location.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'driver_id' => $this->driver->id,
            'driver_name' => $this->driver->name,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'route_id' => $this->routeId,
            'shipment_id' => $this->shipmentId,
            'speed' => $this->speed,
            'heading' => $this->heading,
            'is_moving' => $this->isMoving,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
