<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Exception;

class EmailService
{
    /**
     * Send proposal email to client.
     */
    public function sendProposalEmail(Tenant $tenant, $proposal, $client): array
    {
        try {
            if (!$tenant->email_provider) {
                return [
                    'success' => false,
                    'message' => 'Provedor de email não configurado para este tenant.',
                ];
            }

            if (!$client->email) {
                return [
                    'success' => false,
                    'message' => 'Cliente não possui email cadastrado.',
                ];
            }

            // Configure email provider
            $this->configureEmailProvider($tenant);

            // Send email
            Mail::to($client->email)->send(
                new \App\Mail\ProposalCreated($proposal, $client, $tenant)
            );

            Log::info('Email de proposta enviado', [
                'tenant_id' => $tenant->id,
                'proposal_id' => $proposal->id,
                'client_email' => $client->email,
            ]);

            return [
                'success' => true,
                'message' => 'Email enviado com sucesso.',
            ];
        } catch (Exception $e) {
            Log::error('Erro ao enviar email de proposta', [
                'tenant_id' => $tenant->id,
                'proposal_id' => $proposal->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Erro ao enviar email: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Send test email.
     */
    public function sendTestEmail(Tenant $tenant, string $toEmail): array
    {
        try {
            if (!$tenant->email_provider) {
                return [
                    'success' => false,
                    'message' => 'Provedor de email não configurado.',
                ];
            }

            // Configure email provider
            $this->configureEmailProvider($tenant);

            // Send test email
            Mail::to($toEmail)->send(
                new \App\Mail\TestEmail($tenant)
            );

            Log::info('Email de teste enviado', [
                'tenant_id' => $tenant->id,
                'to_email' => $toEmail,
            ]);

            return [
                'success' => true,
                'message' => 'Email de teste enviado com sucesso.',
            ];
        } catch (Exception $e) {
            Log::error('Erro ao enviar email de teste', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Erro ao enviar email de teste: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Configure email provider based on tenant settings.
     */
    protected function configureEmailProvider(Tenant $tenant): void
    {
        $emailConfig = $tenant->email_config ?? [];

        switch ($tenant->email_provider) {
            case 'postmark':
                $this->configurePostmark($emailConfig);
                break;
            case 'mailchimp':
                $this->configureMailchimp($emailConfig);
                break;
            case 'smtp':
                $this->configureSmtp($emailConfig);
                break;
            default:
                throw new Exception('Provedor de email não suportado: ' . $tenant->email_provider);
        }
    }

    /**
     * Configure Postmark.
     */
    protected function configurePostmark(array $config): void
    {
        Config::set('mail.default', 'postmark');
        Config::set('services.postmark.token', $config['api_token'] ?? null);
        Config::set('mail.mailers.postmark.transport', 'postmark');
        Config::set('mail.from.address', $config['from_email'] ?? config('mail.from.address'));
        Config::set('mail.from.name', $config['from_name'] ?? config('mail.from.name'));
        
        // Set environment variable for Postmark package
        if (isset($config['api_token'])) {
            putenv('POSTMARK_TOKEN=' . $config['api_token']);
            $_ENV['POSTMARK_TOKEN'] = $config['api_token'];
        }
    }

    /**
     * Configure Mailchimp (Mandrill).
     */
    protected function configureMailchimp(array $config): void
    {
        // Mailchimp uses Mandrill for transactional emails
        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp.host', 'smtp.mandrillapp.com');
        Config::set('mail.mailers.smtp.port', 587);
        Config::set('mail.mailers.smtp.encryption', 'tls');
        Config::set('mail.mailers.smtp.username', $config['from_email'] ?? null);
        Config::set('mail.mailers.smtp.password', $config['api_key'] ?? null);
        Config::set('mail.from.address', $config['from_email'] ?? config('mail.from.address'));
        Config::set('mail.from.name', $config['from_name'] ?? config('mail.from.name'));
    }

    /**
     * Configure SMTP.
     */
    protected function configureSmtp(array $config): void
    {
        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp.host', $config['host'] ?? null);
        Config::set('mail.mailers.smtp.port', $config['port'] ?? 587);
        Config::set('mail.mailers.smtp.encryption', $config['encryption'] ?? 'tls');
        Config::set('mail.mailers.smtp.username', $config['username'] ?? null);
        Config::set('mail.mailers.smtp.password', $config['password'] ?? null);
        Config::set('mail.from.address', $config['from_email'] ?? config('mail.from.address'));
        Config::set('mail.from.name', $config['from_name'] ?? config('mail.from.name'));
    }
}
