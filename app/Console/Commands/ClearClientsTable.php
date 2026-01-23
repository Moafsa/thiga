<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\ClientAddress;
use App\Models\ClientLoginCode;
use App\Models\ClientUser;
use App\Models\FreightTable;
use App\Models\Invoice;
use App\Models\Proposal;
use App\Models\Shipment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClearClientsTable extends Command
{
    protected $signature = 'clients:clear
                            {--force : Não pedir confirmação}
                            {--tenant= : Limpar apenas clientes do tenant (ID). Se vazio, limpa todos.}';

    protected $description = 'Limpa a tabela de clientes e todos os dados dependentes (propostas, faturas, entregas, etc.) para teste do zero.';

    public function handle(): int
    {
        if (!$this->option('force') && !$this->confirm('Isso vai APAGAR todos os clientes e dados relacionados (propostas, faturas, entregas, endereços, etc.). Continuar?')) {
            $this->info('Operação cancelada.');
            return 0;
        }

        $tenantId = $this->option('tenant') ? (int) $this->option('tenant') : null;

        DB::beginTransaction();
        try {
            Schema::disableForeignKeyConstraints();

            $clientIds = $tenantId
                ? Client::where('tenant_id', $tenantId)->pluck('id')->toArray()
                : null;

            if ($tenantId && empty($clientIds)) {
                $this->warn("Nenhum cliente encontrado para o tenant {$tenantId}.");
                Schema::enableForeignKeyConstraints();
                DB::commit();
                return 0;
            }

            $qClients = $tenantId ? Client::where('tenant_id', $tenantId) : Client::query();
            $qLogin = $tenantId && !empty($clientIds) ? ClientLoginCode::whereIn('client_id', $clientIds) : ClientLoginCode::query();
            $qUsers = $tenantId && !empty($clientIds) ? ClientUser::whereIn('client_id', $clientIds) : ClientUser::query();
            $qAddr = $tenantId && !empty($clientIds) ? ClientAddress::whereIn('client_id', $clientIds) : ClientAddress::query();

            $this->info('Limpando client_login_codes...');
            $n1 = $qLogin->delete();

            $this->info('Limpando client_users...');
            $n2 = $qUsers->delete();

            $this->info('Limpando client_addresses...');
            $n3 = $qAddr->delete();

            $this->info('Limpando client_freight_table...');
            $qPivot = DB::table('client_freight_table');
            if ($tenantId && !empty($clientIds)) {
                $qPivot->whereIn('client_id', $clientIds);
            }
            $n4 = $qPivot->delete();

            $this->info('Desvinculando client_id em freight_tables...');
            $qFt = FreightTable::whereNotNull('client_id');
            if ($tenantId) {
                $qFt->where('tenant_id', $tenantId);
            }
            $n5 = $qFt->update(['client_id' => null]);

            $qInv = Invoice::query();
            $qProp = Proposal::query();
            $qShip = Shipment::query();
            if ($tenantId && !empty($clientIds)) {
                $qInv->whereIn('client_id', $clientIds);
                $qProp->whereIn('client_id', $clientIds);
                $qShip->where(function ($q) use ($clientIds) {
                    $q->whereIn('sender_client_id', $clientIds)->orWhereIn('receiver_client_id', $clientIds);
                });
            }

            $this->info('Limpando invoices e invoice_items...');
            $n6 = $qInv->delete();

            $this->info('Limpando proposals e available_cargo...');
            $n7 = $qProp->delete();

            $this->info('Limpando shipments (e fiscal_documents, delivery_proofs, etc.)...');
            $n8 = $qShip->delete();

            $this->info('Limpando clients...');
            $n9 = $qClients->delete();

            Schema::enableForeignKeyConstraints();
            DB::commit();

            $this->info('Tabela de clientes e dependentes limpos com sucesso. Você pode testar o cadastro do zero.');
            if ($tenantId) {
                $this->info("Removidos {$n9} cliente(s) do tenant {$tenantId}.");
            } else {
                $this->info("Removidos {$n9} cliente(s) no total.");
            }
        } catch (\Throwable $e) {
            Schema::enableForeignKeyConstraints();
            DB::rollBack();
            $this->error('Erro ao limpar: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
