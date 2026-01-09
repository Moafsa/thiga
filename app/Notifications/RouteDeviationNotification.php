<?php

namespace App\Notifications;

use App\Models\Route;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class RouteDeviationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Route $route;
    protected float $offRouteDistance;
    protected float $extraCost;

    /**
     * Create a new notification instance.
     */
    public function __construct(Route $route, float $offRouteDistance, float $extraCost)
    {
        $this->route = $route;
        $this->offRouteDistance = $offRouteDistance;
        $this->extraCost = $extraCost;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $driver = $this->route->driver;
        $driverName = $driver ? $driver->name : 'Motorista desconhecido';
        
        return [
            'type' => 'route_deviation',
            'route_id' => $this->route->id,
            'route_name' => $this->route->name,
            'driver_id' => $driver ? $driver->id : null,
            'driver_name' => $driverName,
            'off_route_distance_km' => round($this->offRouteDistance, 2),
            'extra_cost' => round($this->extraCost, 2),
            'urgency' => 'warning',
            'message' => "O motorista {$driverName} saiu da rota planejada. Distância fora da rota: " . number_format($this->offRouteDistance, 2, ',', '.') . " km. Custo extra estimado: R$ " . number_format($this->extraCost, 2, ',', '.'),
            'title' => 'Desvio de Rota Detectado',
            'url' => route('monitoring.index'),
        ];
    }
}

