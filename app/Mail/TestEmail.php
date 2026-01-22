<?php

namespace App\Mail;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestEmail extends Mailable
{
    use Queueable, SerializesModels;

    public Tenant $tenant;

    /**
     * Create a new message instance.
     */
    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $emailConfig = $this->tenant->email_config ?? [];
        $fromEmail = $emailConfig['from_email'] ?? config('mail.from.address');
        $fromName = $emailConfig['from_name'] ?? $this->tenant->name ?? config('mail.from.name');

        return $this->from($fromEmail, $fromName)
                    ->subject('Email de Teste - Configuração de Email')
                    ->view('emails.test-email')
                    ->with([
                        'tenant' => $this->tenant,
                    ]);
    }
}
