<?php

namespace App\Mail;

use App\Models\Proposal;
use App\Models\Client;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProposalCreated extends Mailable
{
    use Queueable, SerializesModels;

    public Proposal $proposal;
    public Client $client;
    public Tenant $tenant;

    /**
     * Create a new message instance.
     */
    public function __construct(Proposal $proposal, Client $client, Tenant $tenant)
    {
        $this->proposal = $proposal;
        $this->client = $client;
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
                    ->subject('Nova Proposta Comercial - ' . $this->proposal->proposal_number)
                    ->view('emails.proposal-created')
                    ->with([
                        'proposal' => $this->proposal,
                        'client' => $this->client,
                        'tenant' => $this->tenant,
                    ]);
    }
}
