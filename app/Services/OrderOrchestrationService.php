<?php

namespace App\Services;

use App\Events\OrderOrchestrated;
use App\Models\ApiRequest;
use App\Models\Client;
use App\Models\ClientAddress;
use App\Models\Driver;
use App\Models\Proposal;
use App\Models\Route;
use App\Models\Shipment;
use App\Models\Tenant;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Carbon;

class OrderOrchestrationService
{
    public function __construct(
        protected FreightCalculationService $freightCalculationService
    ) {
    }

    /**
     * Orchestrate full workflow.
     *
     * @return array{idempotent: bool, data: array<string, mixed>}
     */
    public function orchestrate(Tenant $tenant, array $payload, ?string $idempotencyKey = null): array
    {
        $keyHash = $idempotencyKey ? hash('sha256', $idempotencyKey) : null;

        if ($keyHash) {
            $existing = ApiRequest::query()
                ->where('tenant_id', $tenant->id)
                ->byKeyHash($keyHash)
                ->first();

            if ($existing) {
                return [
                    'idempotent' => true,
                    'data' => $existing->response_payload ?? [],
                ];
            }
        }

        $result = DB::transaction(function () use ($tenant, $payload, $keyHash, $idempotencyKey) {
            $customerData = Arr::get($payload, 'customer', []);
            $freightData = Arr::get($payload, 'freight', []);
            $shipmentData = Arr::get($payload, 'shipment', []);
            $proposalData = Arr::get($payload, 'proposal', []);
            $routeData = Arr::get($payload, 'route', []);
            $notificationsData = Arr::get($payload, 'notifications', []);
            $metadata = Arr::get($payload, 'metadata', []);

            $client = $this->resolveClient($tenant, $customerData);

            $items = $this->normalizeItems(Arr::get($shipmentData, 'items', []));
            $aggregated = $this->aggregateItems($items, $freightData);

            $calculation = $this->freightCalculationService->calculate(
                tenant: $tenant,
                destination: (string) Arr::get($freightData, 'destination'),
                weight: (float) $aggregated['weight'],
                cubage: (float) $aggregated['cubage'],
                invoiceValue: (float) $aggregated['invoice_value'],
                options: Arr::get($freightData, 'options', [])
            );

            $proposal = $this->createProposal($tenant, $client, $proposalData, $calculation['total'] ?? 0.0);
            $receiver = $this->resolveReceiverClient($tenant, $shipmentData, $client);
            $shipment = $this->createShipment($tenant, $client, $receiver, $shipmentData, $aggregated, $calculation, $metadata);

            $routeInfo = $this->handleRouteAssignment($tenant, $routeData, $shipment, $client);

            $notifications = $this->prepareNotifications($notificationsData, $client, $shipment);

            $response = $this->buildResponsePayload(
                $tenant,
                $client,
                $proposal,
                $shipment,
                $routeInfo,
                $calculation,
                $notifications,
                $metadata,
                $idempotencyKey
            );

            if ($keyHash) {
                ApiRequest::create([
                    'tenant_id' => $tenant->id,
                    'key_hash' => $keyHash,
                    'request_payload' => $payload,
                    'response_payload' => $response,
                ]);
            }

            event(new OrderOrchestrated(
                tenant: $tenant,
                customer: $client,
                proposal: $proposal,
                shipment: $shipment,
                route: $routeInfo['route'] ?? null,
                context: [
                    'calculation' => $calculation,
                    'notifications' => $notifications,
                    'metadata' => $metadata,
                    'idempotency_key' => $idempotencyKey,
                ]
            ));

            return [
                'idempotent' => false,
                'data' => $response,
            ];
        });

        return $result;
    }

    protected function resolveClient(Tenant $tenant, array $customerData): Client
    {
        $document = Arr::get($customerData, 'document');
        $normalizedDocument = $document ? preg_replace('/\D+/', '', $document) : null;
        $email = Arr::get($customerData, 'email');

        $query = Client::query()->where('tenant_id', $tenant->id);

        if ($normalizedDocument) {
            $client = (clone $query)->where('cnpj', $normalizedDocument)->first();
        } elseif ($email) {
            $client = (clone $query)->where('email', $email)->first();
        } else {
            $client = null;
        }

        $attributes = [
            'tenant_id' => $tenant->id,
            'name' => Arr::get($customerData, 'name'),
            'cnpj' => $normalizedDocument,
            'email' => $email,
            'phone' => Arr::get($customerData, 'phone'),
            'address' => Arr::get($customerData, 'addresses.0.street'),
            'city' => Arr::get($customerData, 'addresses.0.city'),
            'state' => Arr::get($customerData, 'addresses.0.state'),
            'zip_code' => Arr::get($customerData, 'addresses.0.zip_code'),
            'salesperson_id' => Arr::get($customerData, 'salesperson_id'),
            'is_active' => true,
        ];

        $allowUpdate = Arr::get($customerData, 'allow_update', true);

        if ($client) {
            if ($allowUpdate) {
                $client->fill(array_filter($attributes, fn ($value) => !is_null($value)))->save();
                $this->syncClientAddresses($client, Arr::get($customerData, 'addresses', []));
            }
        } else {
            $client = Client::create($attributes);
            $this->syncClientAddresses($client, Arr::get($customerData, 'addresses', []));
        }

        return $client;
    }

    protected function syncClientAddresses(Client $client, array $addresses): void
    {
        if (empty($addresses)) {
            return;
        }

        foreach ($addresses as $addressData) {
            $zip = Arr::get($addressData, 'zip_code');
            $type = Arr::get($addressData, 'type', 'other');

            $existing = $client->addresses()
                ->where('type', $type)
                ->when($zip, fn ($query) => $query->where('zip_code', $zip))
                ->first();

            $payload = [
                'type' => $type,
                'name' => Arr::get($addressData, 'name'),
                'address' => Arr::get($addressData, 'street'),
                'number' => Arr::get($addressData, 'number'),
                'complement' => Arr::get($addressData, 'complement'),
                'neighborhood' => Arr::get($addressData, 'neighborhood'),
                'city' => Arr::get($addressData, 'city'),
                'state' => Arr::get($addressData, 'state'),
                'zip_code' => $zip,
                'is_default' => Arr::get($addressData, 'is_default', false),
            ];

            if ($existing) {
                $existing->update($payload);
            } else {
                $client->addresses()->create($payload);
            }
        }
    }

    protected function normalizeItems(array $items): array
    {
        return collect($items)
            ->map(function ($item) {
                return [
                    'description' => Arr::get($item, 'description'),
                    'quantity' => (int) (Arr::get($item, 'quantity') ?? 1),
                    'weight' => (float) (Arr::get($item, 'weight') ?? 0),
                    'volume' => (float) (Arr::get($item, 'volume') ?? 0),
                    'value' => (float) (Arr::get($item, 'value') ?? 0),
                    'nfe_key' => Arr::get($item, 'nfe_key'),
                ];
            })
            ->values()
            ->all();
    }

    protected function aggregateItems(array $items, array $freightData): array
    {
        $quantity = collect($items)->sum('quantity') ?: 1;
        $weight = collect($items)->sum(function ($item) {
            return $item['weight'] * max($item['quantity'], 1);
        });
        $volume = collect($items)->sum(function ($item) {
            return $item['volume'] * max($item['quantity'], 1);
        });
        $value = collect($items)->sum(function ($item) {
            return $item['value'] * max($item['quantity'], 1);
        });

        return [
            'quantity' => $quantity,
            'weight' => $weight ?: (float) Arr::get($freightData, 'weight', 0),
            'cubage' => $volume ?: (float) Arr::get($freightData, 'cubage', 0),
            'invoice_value' => $value ?: (float) Arr::get($freightData, 'invoice_value', 0),
            'items' => $items,
        ];
    }

    protected function createProposal(Tenant $tenant, Client $client, array $proposalData, float $baseValue): Proposal
    {
        $discountPercentage = (float) Arr::get($proposalData, 'discount_percentage', 0);
        $discountValue = ($baseValue * $discountPercentage) / 100;
        $finalValue = $baseValue - $discountValue;

        return Proposal::create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'salesperson_id' => Arr::get($proposalData, 'salesperson_id') ?? $client->salesperson_id,
            'proposal_number' => $this->generateProposalNumber(),
            'title' => Arr::get($proposalData, 'title') ?? sprintf('Proposta Frete %s', now()->format('d/m/Y')),
            'description' => Arr::get($proposalData, 'notes'),
            'base_value' => $baseValue,
            'discount_percentage' => $discountPercentage,
            'discount_value' => $discountValue,
            'final_value' => $finalValue,
            'valid_until' => Arr::get($proposalData, 'valid_until'),
            'notes' => Arr::get($proposalData, 'notes'),
            'status' => 'draft',
        ]);
    }

    protected function generateProposalNumber(): string
    {
        do {
            $number = 'PROP-' . Str::upper(Str::random(8));
        } while (Proposal::where('proposal_number', $number)->exists());

        return $number;
    }

    protected function resolveReceiverClient(Tenant $tenant, array $shipmentData, Client $fallback): Client
    {
        $delivery = Arr::get($shipmentData, 'delivery', []);
        $name = Arr::get($delivery, 'contact_name') ?: Arr::get($delivery, 'address');
        $zip = Arr::get($delivery, 'zip_code');

        if (!$name) {
            return $fallback;
        }

        $query = Client::query()
            ->where('tenant_id', $tenant->id)
            ->where('name', $name);

        if ($zip) {
            $query->where('zip_code', $zip);
        }

        $client = $query->first();

        $attributes = [
            'tenant_id' => $tenant->id,
            'name' => $name,
            'email' => Arr::get($delivery, 'contact_email'),
            'phone' => Arr::get($delivery, 'contact_phone'),
            'address' => Arr::get($delivery, 'address'),
            'city' => Arr::get($delivery, 'city'),
            'state' => Arr::get($delivery, 'state'),
            'zip_code' => $zip,
            'salesperson_id' => $fallback->salesperson_id,
            'is_active' => true,
        ];

        if ($client) {
            $client->fill(array_filter($attributes, fn ($value) => !is_null($value)))->save();
        } else {
            $client = Client::create($attributes);

            ClientAddress::create([
                'client_id' => $client->id,
                'type' => 'delivery',
                'name' => $name,
                'address' => Arr::get($delivery, 'address'),
                'number' => null,
                'complement' => null,
                'neighborhood' => null,
                'city' => Arr::get($delivery, 'city'),
                'state' => Arr::get($delivery, 'state'),
                'zip_code' => $zip,
                'is_default' => true,
            ]);
        }

        return $client;
    }

    protected function createShipment(
        Tenant $tenant,
        Client $sender,
        Client $receiver,
        array $shipmentData,
        array $aggregated,
        array $calculation,
        array $metadata
    ): Shipment {
        $pickup = Arr::get($shipmentData, 'pickup', []);
        $delivery = Arr::get($shipmentData, 'delivery', []);

        $trackingNumber = $this->generateTrackingNumber();

        $shipment = Shipment::create([
            'tenant_id' => $tenant->id,
            'sender_client_id' => $sender->id,
            'receiver_client_id' => $receiver->id,
            'tracking_number' => $trackingNumber,
            'title' => Arr::get($shipmentData, 'title') ?? sprintf('Carga %s', $sender->name),
            'description' => Arr::get($shipmentData, 'notes'),
            'weight' => $aggregated['weight'],
            'volume' => $aggregated['cubage'],
            'quantity' => $aggregated['quantity'],
            'value' => $aggregated['invoice_value'],
            'pickup_address' => Arr::get($pickup, 'address'),
            'pickup_city' => Arr::get($pickup, 'city'),
            'pickup_state' => Arr::get($pickup, 'state'),
            'pickup_zip_code' => Arr::get($pickup, 'zip_code'),
            'pickup_latitude' => Arr::get($pickup, 'latitude'),
            'pickup_longitude' => Arr::get($pickup, 'longitude'),
            'delivery_address' => Arr::get($delivery, 'address'),
            'delivery_city' => Arr::get($delivery, 'city'),
            'delivery_state' => Arr::get($delivery, 'state'),
            'delivery_zip_code' => Arr::get($delivery, 'zip_code'),
            'delivery_latitude' => Arr::get($delivery, 'latitude'),
            'delivery_longitude' => Arr::get($delivery, 'longitude'),
            'pickup_date' => Arr::get($pickup, 'date'),
            'pickup_time' => Arr::get($pickup, 'time') ? Carbon::parse(Arr::get($pickup, 'time'))->format('H:i') : '08:00',
            'delivery_date' => Arr::get($delivery, 'date') ?? Arr::get($pickup, 'date'),
            'delivery_time' => Arr::get($delivery, 'time') ? Carbon::parse(Arr::get($delivery, 'time'))->format('H:i') : '18:00',
            'status' => 'pending',
            'notes' => Arr::get($shipmentData, 'notes'),
            'metadata' => [
                'freight_calculation' => $calculation,
                'items' => $aggregated['items'],
                'request_source' => Arr::get($metadata, 'source'),
                'conversation_id' => Arr::get($metadata, 'conversation_id'),
            ],
        ]);

        return $shipment;
    }

    protected function generateTrackingNumber(): string
    {
        do {
            $number = 'THG' . Str::upper(Str::random(8));
        } while (Shipment::where('tracking_number', $number)->exists());

        return $number;
    }

    /**
     * @return array{created: bool, route: Route|null}
     */
    protected function handleRouteAssignment(Tenant $tenant, array $routeData, Shipment $shipment, Client $client): array
    {
        $autoAssign = Arr::get($routeData, 'auto_assign', false);

        if (!$autoAssign) {
            return [
                'created' => false,
                'route' => null,
            ];
        }

        $scheduledDate = Arr::get($routeData, 'scheduled_date') ?? $shipment->pickup_date;
        $scheduledDateCarbon = $scheduledDate ? Carbon::parse($scheduledDate) : null;
        $driverId = Arr::get($routeData, 'driver_id');
        $driver = null;

        if ($driverId) {
            $driver = Driver::where('tenant_id', $tenant->id)->find($driverId);

            if (!$driver) {
                throw ValidationException::withMessages([
                    'route.driver_id' => __('Driver not found for this tenant.'),
                ]);
            }
        }

        $existingRoute = Route::query()
            ->where('tenant_id', $tenant->id)
            ->when($driver, fn ($q) => $q->where('driver_id', $driver->id))
            ->when($scheduledDateCarbon, fn ($q) => $q->whereDate('scheduled_date', $scheduledDateCarbon))
            ->where('status', 'scheduled')
            ->first();

        if ($existingRoute) {
            $shipment->route()->associate($existingRoute);
            if ($driver) {
                $shipment->driver()->associate($driver);
            }
            $shipment->save();

            return [
                'created' => false,
                'route' => $existingRoute->fresh(),
            ];
        }

        $route = Route::create([
            'tenant_id' => $tenant->id,
            'driver_id' => $driver?->id,
            'name' => sprintf('Rota %s - %s', $scheduledDateCarbon?->format('d/m') ?? now()->format('d/m'), Str::limit($client->name, 20)),
            'description' => 'Rota criada automaticamente via orquestração MCP.',
            'scheduled_date' => $scheduledDateCarbon ?? now(),
            'status' => 'scheduled',
            'notes' => 'Gerada automaticamente pelo endpoint de orquestração.',
        ]);

        $shipment->route()->associate($route);
        if ($driver) {
            $shipment->driver()->associate($driver);
        }
        $shipment->save();

        return [
            'created' => true,
            'route' => $route->fresh(),
        ];
    }

    protected function prepareNotifications(array $notificationsData, Client $client, Shipment $shipment): array
    {
        $sendWhatsapp = (bool) Arr::get($notificationsData, 'send_whatsapp', false);

        return [
            'whatsapp_enqueued' => $sendWhatsapp,
            'customer_phone' => Arr::get($notificationsData, 'customer_phone') ?? $client->phone,
            'channel_reference' => null,
            'tracking_code' => $shipment->tracking_number,
        ];
    }

    protected function buildResponsePayload(
        Tenant $tenant,
        Client $client,
        Proposal $proposal,
        Shipment $shipment,
        array $routeInfo,
        array $calculation,
        array $notifications,
        array $metadata,
        ?string $idempotencyKey
    ): array {
        $route = $routeInfo['route'] ?? null;
        $freightBreakdown = $calculation['breakdown'] ?? [];

        return [
            'tenant_id' => $tenant->id,
            'customer' => [
                'id' => $client->id,
                'name' => $client->name,
                'document' => $client->cnpj,
                'link' => route('clients.show', $client),
            ],
            'proposal' => [
                'id' => $proposal->id,
                'number' => $proposal->proposal_number,
                'status' => $proposal->status,
                'base_value' => (float) $proposal->base_value,
                'final_value' => (float) $proposal->final_value,
                'valid_until' => optional($proposal->valid_until)->toDateString(),
                'link' => route('proposals.show', $proposal),
            ],
            'shipment' => [
                'id' => $shipment->id,
                'tracking_number' => $shipment->tracking_number,
                'status' => $shipment->status,
                'freight_value' => Arr::get($calculation, 'total'),
                'pickup' => [
                    'address' => $shipment->pickup_address,
                    'scheduled_for' => optional($shipment->pickup_date)->toDateString() . ' ' . $shipment->pickup_time,
                ],
                'delivery' => [
                    'address' => $shipment->delivery_address,
                    'scheduled_for' => optional($shipment->delivery_date)->toDateString() . ' ' . $shipment->delivery_time,
                ],
                'link' => route('shipments.show', $shipment),
                'public_tracking_url' => url("/api/v1/track-shipment?tracking_number={$shipment->tracking_number}"),
            ],
            'route' => $route ? [
                'created' => $routeInfo['created'],
                'id' => $route->id,
                'name' => $route->name,
                'status' => $route->status,
                'link' => route('routes.show', $route),
            ] : [
                'created' => false,
                'id' => null,
                'name' => null,
                'status' => null,
                'link' => null,
            ],
            'freight_breakdown' => [
                'chargeable_weight' => Arr::get($freightBreakdown, 'chargeable_weight'),
                'real_weight' => Arr::get($freightBreakdown, 'real_weight'),
                'volumetric_weight' => Arr::get($freightBreakdown, 'volumetric_weight'),
                'freight_weight' => Arr::get($freightBreakdown, 'freight_weight'),
                'weight_breakdown' => Arr::get($freightBreakdown, 'weight_breakdown'),
                'ad_valorem' => Arr::get($freightBreakdown, 'ad_valorem'),
                'gris' => Arr::get($freightBreakdown, 'gris'),
                'toll' => Arr::get($freightBreakdown, 'toll'),
                'additional_services' => Arr::get($freightBreakdown, 'additional_services'),
                'minimum_applied' => Arr::get($freightBreakdown, 'minimum_applied'),
                'minimum_value' => Arr::get($freightBreakdown, 'minimum_value'),
            ],
            'notifications' => $notifications,
            'metadata' => array_filter([
                'source' => Arr::get($metadata, 'source'),
                'conversation_id' => Arr::get($metadata, 'conversation_id'),
                'idempotency_key' => $idempotencyKey,
            ]),
        ];
    }
}

