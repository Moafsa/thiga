<?php

namespace App\Services;

use App\Models\Proposal;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;
use Exception;

class ProposalNotificationService
{
    protected EmailService $emailService;
    protected WuzApiService $wuzApiService;

    public function __construct(
        EmailService $emailService,
        WuzApiService $wuzApiService
    ) {
        $this->emailService = $emailService;
        $this->wuzApiService = $wuzApiService;
    }

    /**
     * Send proposal notifications (email and/or WhatsApp).
     */
    public function sendProposalNotifications(Proposal $proposal, bool $sendEmail = false, bool $sendWhatsApp = false): array
    {
        $results = [
            'email' => ['success' => false, 'message' => ''],
            'whatsapp' => ['success' => false, 'message' => ''],
        ];

        $tenant = $proposal->tenant;
        $client = $proposal->client;

        if (!$tenant || !$client) {
            Log::warning('Tenant ou cliente não encontrado para envio de notificações', [
                'proposal_id' => $proposal->id,
            ]);
            return $results;
        }

        // Se não especificado, usa as configurações padrão do tenant
        if (!$sendEmail && !$sendWhatsApp) {
            $sendEmail = $tenant->send_proposal_by_email ?? false;
            $sendWhatsApp = $tenant->send_proposal_by_whatsapp ?? false;
        }

        // Send email if requested
        if ($sendEmail) {
            try {
                $emailResult = $this->emailService->sendProposalEmail($tenant, $proposal, $client);
                $results['email'] = $emailResult;
            } catch (Exception $e) {
                Log::error('Erro ao enviar email de proposta', [
                    'proposal_id' => $proposal->id,
                    'error' => $e->getMessage(),
                ]);
                $results['email'] = [
                    'success' => false,
                    'message' => 'Erro ao enviar email: ' . $e->getMessage(),
                ];
            }
        }

        // Send WhatsApp if requested
        if ($sendWhatsApp) {
            try {
                $whatsappResult = $this->sendProposalWhatsApp($tenant, $proposal, $client);
                $results['whatsapp'] = $whatsappResult;
            } catch (Exception $e) {
                Log::error('Erro ao enviar WhatsApp de proposta', [
                    'proposal_id' => $proposal->id,
                    'error' => $e->getMessage(),
                ]);
                $results['whatsapp'] = [
                    'success' => false,
                    'message' => 'Erro ao enviar WhatsApp: ' . $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Send proposal via WhatsApp.
     */
    protected function sendProposalWhatsApp(Tenant $tenant, Proposal $proposal, $client): array
    {
        try {
            if (!$client->phone_e164 && !$client->phone) {
                return [
                    'success' => false,
                    'message' => 'Cliente não possui telefone cadastrado.',
                ];
            }

            // Get active WhatsApp integration
            $whatsappIntegration = $tenant->whatsappIntegrations()
                ->where('status', 'connected')
                ->first();

            if (!$whatsappIntegration) {
                return [
                    'success' => false,
                    'message' => 'Nenhuma integração WhatsApp ativa encontrada.',
                ];
            }

            $token = $whatsappIntegration->getUserToken();
            if (!$token) {
                return [
                    'success' => false,
                    'message' => 'Token do WhatsApp não disponível.',
                ];
            }

            // Format phone number
            $phone = $client->phone_e164 ?? $client->phone;
            $phone = preg_replace('/\D/', '', $phone);
            
            // Ensure E.164 format
            if (!str_starts_with($phone, '55') && strlen($phone) >= 10) {
                $phone = '55' . $phone;
            }

            // Build message
            $message = $this->buildProposalWhatsAppMessage($proposal, $client, $tenant);

            // Send message
            $this->wuzApiService->sendTextMessage($token, $phone, $message);

            Log::info('WhatsApp de proposta enviado', [
                'tenant_id' => $tenant->id,
                'proposal_id' => $proposal->id,
                'client_phone' => $phone,
            ]);

            return [
                'success' => true,
                'message' => 'WhatsApp enviado com sucesso.',
            ];
        } catch (Exception $e) {
            Log::error('Erro ao enviar WhatsApp de proposta', [
                'tenant_id' => $tenant->id,
                'proposal_id' => $proposal->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Erro ao enviar WhatsApp: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Build WhatsApp message for proposal.
     */
    protected function buildProposalWhatsAppMessage(Proposal $proposal, $client, Tenant $tenant): string
    {
        $proposalUrl = route('client.proposals.show', $proposal);
        
        $message = "🚚 *Nova Proposta Comercial*\n\n";
        $message .= "Olá, {$client->name}!\n\n";
        $message .= "Uma nova proposta comercial foi criada para você:\n\n";
        $message .= "📋 *Proposta:* {$proposal->proposal_number}\n";
        $message .= "📝 *Título:* {$proposal->title}\n";
        $message .= "💰 *Valor:* R$ " . number_format($proposal->final_value, 2, ',', '.') . "\n";
        
        if ($proposal->valid_until) {
            $message .= "📅 *Válida até:* " . $proposal->valid_until->format('d/m/Y') . "\n";
        }
        
        $message .= "\n";
        $message .= "Acesse a proposta completa através do link:\n";
        $message .= $proposalUrl . "\n\n";
        $message .= "Atenciosamente,\n";
        $message .= $tenant->name;

        return $message;
    }
}
