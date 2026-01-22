<?php

namespace App\Mail;

use App\Models\Client;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClientLoginCodeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Client $client,
        public Tenant $tenant,
        public string $code,
        public string $expiresAtFormatted
    ) {
    }

    public function build()
    {
        $emailConfig = $this->tenant->email_config ?? [];
        $fromEmail = $emailConfig['from_email'] ?? config('mail.from.address');
        $fromName = $emailConfig['from_name'] ?? $this->tenant->name ?? config('mail.from.name');

        return $this->from($fromEmail, $fromName)
            ->subject('Seu código de acesso - ' . $this->tenant->name)
            ->view('emails.client-login-code')
            ->with([
                'client' => $this->client,
                'tenant' => $this->tenant,
                'code' => $this->code,
                'expiresAtFormatted' => $this->expiresAtFormatted,
            ]);
    }
}
