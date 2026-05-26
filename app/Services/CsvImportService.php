<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ClientUser;
use App\Models\Driver;
use App\Models\DriverTenantAssignment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CsvImportService
{
    /**
     * Parse CSV file into rows.
     * 
     * @param string $filePath
     * @return array
     */
    public function parseCsv(string $filePath): array
    {
        $rows = [];
        if (($handle = fopen($filePath, 'r')) !== false) {
            // Check for UTF-8 BOM and skip it
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }

            $headers = null;
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                // If parsing semicolon-separated instead of comma
                if (count($data) === 1 && strpos($data[0], ';') !== false) {
                    $data = str_getcsv($data[0], ';');
                }

                if (!$headers) {
                    $headers = array_map(function($h) {
                        return strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '', $h)));
                    }, $data);
                } else {
                    $row = [];
                    foreach ($headers as $index => $header) {
                        $row[$header] = isset($data[$index]) ? trim($data[$index]) : null;
                    }
                    $rows[] = $row;
                }
            }
            fclose($handle);
        }
        return $rows;
    }

    /**
     * Bulk import Clients.
     * 
     * @param int $tenantId
     * @param array $rows
     * @return array
     */
    public function importClients(int $tenantId, array $rows): array
    {
        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $line = $index + 2; // header is line 1

            if (empty($row['name'])) {
                $errors[] = "Linha {$line}: O campo 'name' (Nome) é obrigatório.";
                $errorCount++;
                continue;
            }

            $cnpj = $row['cnpj'] ?? null;
            $email = $row['email'] ?? null;
            $phone = $row['phone'] ?? null;

            if (empty($email) && empty($phone)) {
                $errors[] = "Linha {$line}: O cliente '{$row['name']}' deve possuir pelo menos um e-mail ou telefone.";
                $errorCount++;
                continue;
            }

            try {
                // Check duplicate inside tenant
                $duplicate = Client::findDuplicateInTenant($tenantId, [
                    'cnpj' => $cnpj,
                    'phone' => $phone,
                    'email' => $email,
                ]);

                if ($duplicate) {
                    $errors[] = "Linha {$line}: O cliente '{$row['name']}' já possui duplicidade de CNPJ, E-mail ou Telefone no sistema.";
                    $errorCount++;
                    continue;
                }

                DB::transaction(function () use ($row, $tenantId, $cnpj, $email, $phone) {
                    $client = Client::create([
                        'tenant_id' => $tenantId,
                        'name' => $row['name'],
                        'cnpj' => $cnpj,
                        'email' => $email,
                        'phone' => $phone,
                        'address' => $row['address'] ?? null,
                        'city' => $row['city'] ?? null,
                        'state' => !empty($row['state']) ? strtoupper(substr($row['state'], 0, 2)) : null,
                        'zip_code' => $row['zip_code'] ?? null,
                        'is_active' => true,
                        'marker' => 'bronze'
                    ]);

                    $phoneDigits = $phone ? preg_replace('/\D/', '', $phone) : null;
                    $userEmail = $email
                        ? Str::lower($email)
                        : 'client+' . $tenantId . '+' . ($phoneDigits ?? rand(10000, 99999)) . '@tms.local';

                    // Update or create user
                    $user = User::create([
                        'name' => $row['name'],
                        'email' => $userEmail,
                        'password' => Hash::make(Str::random(32)),
                        'tenant_id' => $tenantId,
                        'phone' => $phoneDigits,
                        'is_active' => true,
                    ]);

                    try {
                        $user->assignRole('Client');
                    } catch (\Throwable $e) {
                        Log::warning('Failed to assign role Client to imported user', ['error' => $e->getMessage()]);
                    }

                    ClientUser::create([
                        'client_id' => $client->id,
                        'tenant_id' => $tenantId,
                        'user_id' => $user->id,
                    ]);

                    $client->forceFill(['user_id' => $user->id])->save();
                });

                $successCount++;
            } catch (\Exception $e) {
                $errors[] = "Linha {$line}: Erro ao cadastrar '{$row['name']}': " . $e->getMessage();
                $errorCount++;
            }
        }

        return [
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'errors' => $errors
        ];
    }

    /**
     * Bulk import Drivers.
     * 
     * @param int $tenantId
     * @param array $rows
     * @return array
     */
    public function importDrivers(int $tenantId, array $rows): array
    {
        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $line = $index + 2;

            if (empty($row['name'])) {
                $errors[] = "Linha {$line}: O campo 'name' (Nome) é obrigatório.";
                $errorCount++;
                continue;
            }

            $phone = $row['phone'] ?? null;
            if (empty($phone)) {
                $errors[] = "Linha {$line}: O motorista '{$row['name']}' deve possuir um telefone.";
                $errorCount++;
                continue;
            }

            try {
                $phoneDigits = preg_replace('/\D/', '', $phone);
                $phoneE164 = strlen($phoneDigits) >= 10
                    ? (!str_starts_with($phoneDigits, '55') ? '55' . $phoneDigits : $phoneDigits)
                    : $phoneDigits;

                // Check duplicate in tenant
                $duplicate = Driver::where('tenant_id', $tenantId)
                    ->where(function($q) use ($row, $phone, $phoneE164) {
                        $q->where('phone', $phone)
                          ->orWhere('phone_e164', $phoneE164);
                        if (!empty($row['document'])) {
                            $q->orWhere('document', $row['document']);
                        }
                    })->exists();

                if ($duplicate) {
                    $errors[] = "Linha {$line}: O motorista '{$row['name']}' já possui duplicidade de Telefone ou CPF no sistema.";
                    $errorCount++;
                    continue;
                }

                DB::transaction(function () use ($row, $tenantId, $phone, $phoneDigits, $phoneE164) {
                    $driver = Driver::create([
                        'tenant_id' => $tenantId,
                        'name' => $row['name'],
                        'email' => $row['email'] ?? null,
                        'phone' => $phone,
                        'phone_e164' => $phoneE164,
                        'document' => $row['document'] ?? null,
                        'cnh_number' => $row['cnh_number'] ?? null,
                        'cnh_category' => $row['cnh_category'] ?? null,
                        'cnh_expiry_date' => !empty($row['cnh_expiry_date']) ? date('Y-m-d', strtotime($row['cnh_expiry_date'])) : null,
                        'vehicle_plate' => $row['vehicle_plate'] ?? null,
                        'vehicle_model' => $row['vehicle_model'] ?? null,
                        'vehicle_color' => $row['vehicle_color'] ?? null,
                        'status' => 'available',
                        'is_active' => true,
                        'location_tracking_enabled' => false
                    ]);

                    $sanitizedEmail = Str::lower("driver+{$tenantId}+{$phoneDigits}@tms.local");

                    $user = User::updateOrCreate(
                        ['email' => $sanitizedEmail],
                        [
                            'name' => $row['name'],
                            'password' => Hash::make(Str::random(32)),
                            'tenant_id' => $tenantId,
                            'phone' => $phoneDigits,
                            'is_active' => true,
                        ]
                    );

                    try {
                        $user->assignRole('Driver');
                    } catch (\Throwable $e) {
                        Log::warning('Failed to assign role Driver to imported user', ['error' => $e->getMessage()]);
                    }

                    $driver->forceFill(['user_id' => $user->id])->save();

                    DriverTenantAssignment::firstOrCreate(
                        [
                            'driver_id' => $driver->id,
                            'tenant_id' => $tenantId,
                        ],
                        [
                            'user_id' => $user->id,
                        ]
                    );
                });

                $successCount++;
            } catch (\Exception $e) {
                $errors[] = "Linha {$line}: Erro ao cadastrar '{$row['name']}': " . $e->getMessage();
                $errorCount++;
            }
        }

        return [
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'errors' => $errors
        ];
    }
}
