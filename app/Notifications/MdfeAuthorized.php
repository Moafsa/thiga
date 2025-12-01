<?php

namespace App\Notifications;

use App\Models\FiscalDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MdfeAuthorized extends Notification implements ShouldQueue
{
    use Queueable;

    protected FiscalDocument $fiscalDocument;

    /**
     * Create a new notification instance.
     */
    public function __construct(FiscalDocument $fiscalDocument)
    {
        $this->fiscalDocument = $fiscalDocument;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        $route = $this->fiscalDocument->route;
        
        return [
            'type' => 'mdfe_authorized',
            'fiscal_document_id' => $this->fiscalDocument->id,
            'route_id' => $route->id ?? null,
            'route_name' => $route->name ?? 'N/A',
            'access_key' => $this->fiscalDocument->access_key,
            'message' => "MDF-e authorized for route {$route->name ?? 'N/A'}",
            'url' => $route ? route('routes.show', $route) : null,
        ];
    }
}

















