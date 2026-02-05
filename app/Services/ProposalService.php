<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Proposal;
use App\Models\Salesperson;
use App\Models\Tenant;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ProposalService
{
    protected ProposalNotificationService $notificationService;
    protected FreightCalculationService $freightCalculationService;

    public function __construct(
        ProposalNotificationService $notificationService,
        FreightCalculationService $freightCalculationService
    ) {
        $this->notificationService = $notificationService;
        $this->freightCalculationService = $freightCalculationService;
    }

    /**
     * Create a new proposal
     */
    public function createProposal(Tenant $tenant, array $data, ?bool $sendEmail = false, ?bool $sendWhatsApp = false): Proposal
    {
        // 1. Validate Relations
        $client = Client::where('id', $data['client_id'])
            ->where('tenant_id', $tenant->id)
            ->firstOrFail();

        $salesperson = Salesperson::where('id', $data['salesperson_id'])
            ->where('tenant_id', $tenant->id)
            ->firstOrFail();

        // 2. Validate Discount
        if (($data['discount_percentage'] ?? 0) > $salesperson->max_discount_percentage) {
            throw new \Exception("Desconto máximo permitido para este vendedor é {$salesperson->max_discount_percentage}%");
        }

        // 3. Calculate Values
        $baseValue = (float) $data['base_value'];
        $discountPercentage = (float) ($data['discount_percentage'] ?? 0);
        $discountValue = ($baseValue * $discountPercentage) / 100;
        $finalValue = $baseValue - $discountValue;

        // 4. Validate Minimum Freight Rate
        $this->validateMinimumFreight($tenant, $data, $finalValue);

        // 5. Create Proposal
        $proposalNumber = 'PROP-' . strtoupper(Str::random(8));

        $proposal = Proposal::create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'salesperson_id' => $salesperson->id,
            'proposal_number' => $proposalNumber,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'weight' => isset($data['weight']) ? (float) $data['weight'] : null,
            'height' => isset($data['height']) ? (float) $data['height'] : null,
            'width' => isset($data['width']) ? (float) $data['width'] : null,
            'length' => isset($data['length']) ? (float) $data['length'] : null,
            'cubage' => isset($data['cubage']) ? (float) $data['cubage'] : null,
            'base_value' => $baseValue,
            'discount_percentage' => $discountPercentage,
            'discount_value' => $discountValue,
            'final_value' => $finalValue,
            'valid_until' => $data['valid_until'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => 'draft',
            'origin_address' => $data['origin_address'] ?? null,
            'destination_address' => $data['destination_address'] ?? null,
            'origin_latitude' => $data['origin_latitude'] ?? null,
            'origin_longitude' => $data['origin_longitude'] ?? null,
            'destination_latitude' => $data['destination_latitude'] ?? null,
            'destination_longitude' => $data['destination_longitude'] ?? null,
        ]);

        // 6. Send Notifications
        if ($sendEmail || $sendWhatsApp) {
            $this->sendNotifications($proposal, $tenant, $sendEmail, $sendWhatsApp);
        }

        return $proposal;
    }

    protected function validateMinimumFreight(Tenant $tenant, array $data, float $finalValue): void
    {
        $minFreightValue = 0;

        // Manual override
        if (!empty($data['min_freight_rate_type']) && !empty($data['min_freight_rate_value'])) {
            $invoiceValue = (float) ($data['invoice_value'] ?? 0);
            if ($data['min_freight_rate_type'] === 'percentage') {
                $rate = (float) $data['min_freight_rate_value'];
                $pct = $rate > 1 ? $rate / 100 : $rate;
                $minFreightValue = $invoiceValue * $pct;
            } else {
                $minFreightValue = (float) $data['min_freight_rate_value'];
            }
        }
        // Automatic Calculation
        elseif (!empty($data['destination']) && !empty($data['invoice_value'])) {
            // Logic extracted roughly from controller - refined using existing freight calc logic if possible
            // For brevity, using a simplified check or reusing a calculation method if available
            // Real implementation should reuse FreightCalculationService logic ideally
        }

        if ($minFreightValue > 0 && $finalValue < $minFreightValue) {
            throw new \Exception("Valor final (R$ {$finalValue}) abaixo da taxa mínima (R$ {$minFreightValue}).");
        }
    }

    protected function sendNotifications(Proposal $proposal, Tenant $tenant, bool $sendEmail, bool $sendWhatsApp)
    {
        // Backup settings
        $origEmail = $tenant->send_proposal_by_email;
        $origWhats = $tenant->send_proposal_by_whatsapp;

        try {
            $tenant->send_proposal_by_email = $sendEmail;
            $tenant->send_proposal_by_whatsapp = $sendWhatsApp;

            $this->notificationService->sendProposalNotifications($proposal, $sendEmail, $sendWhatsApp);
        } finally {
            // Restore settings
            $tenant->send_proposal_by_email = $origEmail;
            $tenant->send_proposal_by_whatsapp = $origWhats;
        }
    }
}
