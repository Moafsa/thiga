<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Client;
use App\Models\Shipment;
use App\Models\FreightTable;
use App\Services\FreightCalculationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CreateShipment extends Component
{
    public $step = 1;
    public $totalSteps = 3;

    // Step 1: Remetente e Destinatário
    public $sender_client_id;
    public $receiver_name;
    public $receiver_phone;
    public $receiver_email;

    // Step 1: Endereços
    public $pickup_address;
    public $pickup_city;
    public $pickup_state;
    public $pickup_zip_code;
    public $delivery_address;
    public $delivery_city;
    public $delivery_state;
    public $delivery_zip_code;

    // Step 2: Dados da Mercadoria
    public $title;
    public $description;
    public $weight;
    public $volume;
    public $quantity = 1;
    public $value;

    // Step 2: Datas
    public $pickup_date;
    public $pickup_time = '08:00';
    public $delivery_date;
    public $delivery_time = '18:00';

    // Step 3: Cálculo de Frete e Confirmação
    public $freight_calculation_result = null;
    public $freight_value;
    public $notes;

    public $clients = [];
    public $calculatedFreight = false;

    protected $rules = [
        'sender_client_id' => 'required|exists:clients,id',
        'receiver_name' => 'required|string|max:255',
        'delivery_address' => 'required|string|max:255',
        'delivery_city' => 'required|string|max:255',
        'delivery_state' => 'required|string|size:2',
        'delivery_zip_code' => 'required|string|max:10',
        'pickup_address' => 'required|string|max:255',
        'pickup_city' => 'required|string|max:255',
        'pickup_state' => 'required|string|size:2',
        'pickup_zip_code' => 'required|string|max:10',
        'title' => 'required|string|max:255',
        'pickup_date' => 'required|date',
        'delivery_date' => 'required|date|after_or_equal:pickup_date',
    ];

    protected $messages = [
        'sender_client_id.required' => 'Selecione um remetente',
        'receiver_name.required' => 'Nome do destinatário é obrigatório',
        'delivery_address.required' => 'Endereço de entrega é obrigatório',
        'delivery_city.required' => 'Cidade de entrega é obrigatória',
        'delivery_state.required' => 'Estado de entrega é obrigatório',
        'delivery_zip_code.required' => 'CEP de entrega é obrigatório',
        'pickup_address.required' => 'Endereço de coleta é obrigatório',
        'pickup_city.required' => 'Cidade de coleta é obrigatória',
        'pickup_state.required' => 'Estado de coleta é obrigatório',
        'pickup_zip_code.required' => 'CEP de coleta é obrigatório',
        'title.required' => 'Título/Descrição é obrigatório',
        'pickup_date.required' => 'Data de coleta é obrigatória',
        'delivery_date.required' => 'Data de entrega é obrigatória',
        'delivery_date.after_or_equal' => 'Data de entrega deve ser maior ou igual à data de coleta',
    ];

    public function mount()
    {
        $tenant = Auth::user()->tenant;
        $this->clients = Client::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Set default dates
        $this->pickup_date = now()->format('Y-m-d');
        $this->delivery_date = now()->addDays(1)->format('Y-m-d');
    }

    public function nextStep()
    {
        if ($this->step == 1) {
            $this->validate([
                'sender_client_id' => 'required|exists:clients,id',
                'receiver_name' => 'required|string|max:255',
                'delivery_address' => 'required|string|max:255',
                'delivery_city' => 'required|string|max:255',
                'delivery_state' => 'required|string|size:2',
                'delivery_zip_code' => 'required|string|max:10',
                'pickup_address' => 'required|string|max:255',
                'pickup_city' => 'required|string|max:255',
                'pickup_state' => 'required|string|size:2',
                'pickup_zip_code' => 'required|string|max:10',
            ]);
        } elseif ($this->step == 2) {
            $this->validate([
                'title' => 'required|string|max:255',
                'pickup_date' => 'required|date',
                'delivery_date' => 'required|date|after_or_equal:pickup_date',
            ]);
            
            // Calculate freight automatically when moving to step 3
            $this->calculateFreight();
        }

        if ($this->step < $this->totalSteps) {
            $this->step++;
        }
    }

    public function previousStep()
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function calculateFreight()
    {
        if (!$this->weight || !$this->delivery_zip_code) {
            $this->calculatedFreight = false;
            return;
        }

        try {
            $tenant = Auth::user()->tenant;
            $service = app(FreightCalculationService::class);

            $result = $service->calculate(
                $tenant,
                $this->delivery_zip_code,
                (float) $this->weight,
                (float) ($this->volume ?? 0),
                (float) ($this->value ?? 0),
                []
            );

            $this->freight_calculation_result = $result;
            $this->freight_value = $result['total'];
            $this->calculatedFreight = true;
        } catch (\Exception $e) {
            $this->calculatedFreight = false;
            $this->addError('freight', 'Erro ao calcular frete: ' . $e->getMessage());
        }
    }

    public function updatedWeight()
    {
        if ($this->step == 2) {
            $this->calculateFreight();
        }
    }

    public function updatedVolume()
    {
        if ($this->step == 2) {
            $this->calculateFreight();
        }
    }

    public function updatedValue()
    {
        if ($this->step == 2) {
            $this->calculateFreight();
        }
    }

    public function save()
    {
        $this->validate();

        $tenant = Auth::user()->tenant;

        // Find or create receiver client
        $receiverClient = Client::where('tenant_id', $tenant->id)
            ->where('name', $this->receiver_name)
            ->where('zip_code', $this->delivery_zip_code)
            ->first();

        if (!$receiverClient) {
            $receiverClient = Client::create([
                'tenant_id' => $tenant->id,
                'name' => $this->receiver_name,
                'phone' => $this->receiver_phone ?? null,
                'email' => $this->receiver_email ?? null,
                'address' => $this->delivery_address,
                'city' => $this->delivery_city,
                'state' => $this->delivery_state,
                'zip_code' => $this->delivery_zip_code,
                'is_active' => true,
            ]);
        }

        // Generate tracking number
        $trackingNumber = 'THG' . strtoupper(Str::random(8));

        $shipment = Shipment::create([
            'tenant_id' => $tenant->id,
            'sender_client_id' => $this->sender_client_id,
            'receiver_client_id' => $receiverClient->id,
            'tracking_number' => $trackingNumber,
            'title' => $this->title,
            'description' => $this->description ?? null,
            'weight' => $this->weight ?? null,
            'volume' => $this->volume ?? null,
            'quantity' => $this->quantity ?? 1,
            'value' => $this->value ?? null,
            'pickup_address' => $this->pickup_address,
            'pickup_city' => $this->pickup_city,
            'pickup_state' => $this->pickup_state,
            'pickup_zip_code' => $this->pickup_zip_code,
            'delivery_address' => $this->delivery_address,
            'delivery_city' => $this->delivery_city,
            'delivery_state' => $this->delivery_state,
            'delivery_zip_code' => $this->delivery_zip_code,
            'pickup_date' => $this->pickup_date,
            'pickup_time' => $this->pickup_time ?? '08:00',
            'delivery_date' => $this->delivery_date,
            'delivery_time' => $this->delivery_time ?? '18:00',
            'notes' => $this->notes ?? null,
            'status' => 'pending',
            'metadata' => [
                'freight_value' => $this->freight_value ?? null,
                'freight_calculation' => $this->freight_calculation_result,
            ],
        ]);

        session()->flash('success', 'Carga criada com sucesso! Número de rastreamento: ' . $trackingNumber);
        return redirect()->route('shipments.show', $shipment);
    }

    public function render()
    {
        return view('livewire.create-shipment');
    }
}





















