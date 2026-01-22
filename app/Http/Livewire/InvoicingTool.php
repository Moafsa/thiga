<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Client;
use App\Models\Shipment;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\FreightCalculationService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class InvoicingTool extends Component
{
    public $selectedClientId = null;
    public $startDate;
    public $endDate;
    public $selectedShipments = [];
    public $dueDateDays = 30;
    
    public $clients = [];
    public $availableShipments = [];
    public $selectedClient = null;
    
    protected $rules = [
        'selectedClientId' => 'required|exists:clients,id',
        'startDate' => 'required|date',
        'endDate' => 'required|date|after_or_equal:startDate',
        'dueDateDays' => 'required|integer|min:1|max:365',
    ];

    protected $messages = [
        'selectedClientId.required' => 'Selecione um cliente',
        'startDate.required' => 'Data inicial é obrigatória',
        'endDate.required' => 'Data final é obrigatória',
        'endDate.after_or_equal' => 'Data final deve ser maior ou igual à data inicial',
    ];

    public function mount()
    {
        $tenant = Auth::user()->tenant;
        $this->clients = Client::where('tenant_id', $tenant->id)
            ->listed()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        // Set default dates (last 30 days)
        $this->endDate = Carbon::today()->format('Y-m-d');
        $this->startDate = Carbon::today()->subDays(30)->format('Y-m-d');
    }

    public function updatedSelectedClientId()
    {
        $this->loadAvailableShipments();
    }

    public function updatedStartDate()
    {
        $this->loadAvailableShipments();
    }

    public function updatedEndDate()
    {
        $this->loadAvailableShipments();
    }

    public function loadAvailableShipments()
    {
        if (!$this->selectedClientId || !$this->startDate || !$this->endDate) {
            $this->availableShipments = [];
            return;
        }

        $tenant = Auth::user()->tenant;
        
        $shipments = Shipment::where('tenant_id', $tenant->id)
            ->where('sender_client_id', $this->selectedClientId)
            ->readyForInvoicing()
            ->whereBetween('pickup_date', [$this->startDate, $this->endDate])
            ->with(['senderClient', 'receiverClient', 'fiscalDocuments'])
            ->orderBy('pickup_date', 'desc')
            ->get();

        $this->availableShipments = $shipments->map(function ($shipment) {
                return [
                    'id' => $shipment->id,
                    'tracking_number' => $shipment->tracking_number,
                    'title' => $shipment->title,
                    'pickup_date' => $shipment->pickup_date->format('d/m/Y'),
                    'delivery_city' => $shipment->delivery_city,
                    'delivery_state' => $shipment->delivery_state,
                    'weight' => $shipment->weight,
                    'volume' => $shipment->volume,
                    'value' => $shipment->value,
                    'receiver_name' => $shipment->receiverClient->name ?? 'N/A',
                    'cte_status' => $shipment->hasAuthorizedCte() ? 'Autorizado' : 'Pendente',
                    'freight_value' => $this->calculateFreightValue($shipment),
                ];
            })
            ->values()
            ->toArray();

        $this->selectedShipments = [];
    }

    protected function calculateFreightValue($shipment)
    {
        // Priority 1: Use freight_value from metadata if available (from previous calculation)
        if ($shipment->metadata && isset($shipment->metadata['freight_value'])) {
            return (float) $shipment->metadata['freight_value'];
        }

        // Priority 2: Try to calculate using FreightCalculationService
        try {
            $tenant = Auth::user()->tenant;
            $freightService = app(FreightCalculationService::class);
            
            // Use delivery zip code as destination
            $destination = $shipment->delivery_zip_code ?? $shipment->delivery_city;
            
            if (!$destination) {
                throw new \Exception('Destination not available for freight calculation');
            }
            
            $result = $freightService->calculate(
                $tenant,
                $destination,
                (float) ($shipment->weight ?? 0),
                (float) ($shipment->volume ?? 0),
                (float) ($shipment->value ?? 0),
                []
            );

            return $result['total'] ?? 0;
        } catch (\Exception $e) {
            // If calculation fails, use fallback
            \Log::warning('Freight calculation failed for shipment', [
                'shipment_id' => $shipment->id,
                'error' => $e->getMessage()
            ]);
            
            // Fallback: simple calculation based on value (10% of invoice value)
            return $shipment->value ? ($shipment->value * 0.1) : 100.00;
        }
    }

    public function toggleShipment($shipmentId)
    {
        if (in_array($shipmentId, $this->selectedShipments)) {
            $this->selectedShipments = array_values(array_diff($this->selectedShipments, [$shipmentId]));
        } else {
            $this->selectedShipments[] = $shipmentId;
        }
    }

    public function selectAll()
    {
        $this->selectedShipments = array_column($this->availableShipments, 'id');
    }

    public function deselectAll()
    {
        $this->selectedShipments = [];
    }

    public function generateInvoice()
    {
        $this->validate();

        if (empty($this->selectedShipments)) {
            $this->addError('selectedShipments', 'Selecione pelo menos uma carga para faturar.');
            return;
        }

        $tenant = Auth::user()->tenant;
        $client = Client::findOrFail($this->selectedClientId);

        // Verify all selected shipments belong to the tenant and client
        $shipments = Shipment::where('tenant_id', $tenant->id)
            ->where('sender_client_id', $this->selectedClientId)
            ->whereIn('id', $this->selectedShipments)
            ->readyForInvoicing()
            ->get();

        if ($shipments->count() !== count($this->selectedShipments)) {
            $this->addError('selectedShipments', 'Algumas cargas selecionadas não podem ser faturadas.');
            return;
        }

        // Calculate totals
        $subtotal = 0;
        $items = [];

        foreach ($shipments as $shipment) {
            $freightValue = $this->calculateFreightValue($shipment);
            $subtotal += $freightValue;
            
            $items[] = [
                'shipment_id' => $shipment->id,
                'description' => "Frete: {$shipment->title} (#{$shipment->tracking_number})",
                'quantity' => 1,
                'unit_price' => $freightValue,
                'total_price' => $freightValue,
                'freight_value' => $freightValue,
            ];
        }

        $taxAmount = 0; // Can be configured later
        $totalAmount = $subtotal + $taxAmount;

        // Generate invoice number
        $invoiceNumber = Invoice::generateInvoiceNumber($tenant->id);

        // Create invoice
        $invoice = Invoice::create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'invoice_number' => $invoiceNumber,
            'issue_date' => Carbon::today(),
            'due_date' => Carbon::today()->addDays($this->dueDateDays),
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'status' => 'open',
        ]);

        // Create invoice items
        foreach ($items as $itemData) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'shipment_id' => $itemData['shipment_id'],
                'description' => $itemData['description'],
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['unit_price'],
                'total_price' => $itemData['total_price'],
                'freight_value' => $itemData['freight_value'],
            ]);
        }

        // Clear selections
        $this->selectedShipments = [];
        
        // Redirect to invoice view
        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Fatura gerada com sucesso!');
    }

    public function render()
    {
        return view('livewire.invoicing-tool');
    }
}

