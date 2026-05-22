<?php

namespace App\Http\Livewire\Crm;

use Livewire\Component;
use App\Models\CrmPipeline;
use App\Models\CrmStage;
use App\Models\CrmDeal;
use Illuminate\Support\Facades\Auth;

class CrmBoard extends Component
{
    public $pipeline;
    public $stages;
    public $deals;
    
    public $search = '';

    // Modal properties
    public $showDealModal = false;
    public $selectedDeal = null;

    protected $listeners = ['updateDealStage'];

    public function mount()
    {
        $this->loadPipeline();
    }

    public function updatedSearch()
    {
        $this->loadPipeline();
    }

    public function loadPipeline()
    {
        $tenantId = Auth::user()->tenant_id;

        // Find or create default pipeline
        $this->pipeline = CrmPipeline::firstOrCreate(
            ['tenant_id' => $tenantId, 'is_default' => true],
            ['name' => 'Pipeline Comercial Padrão']
        );

        // Ensure default stages exist
        if ($this->pipeline->stages()->count() === 0) {
            $defaultStages = [
                ['name' => 'Novo Lead', 'order' => 1, 'color' => '#3498db'],
                ['name' => 'Em Negociação', 'order' => 2, 'color' => '#f39c12'],
                ['name' => 'Cotação Enviada', 'order' => 3, 'color' => '#9b59b6'],
                ['name' => 'Fechado/Ganho', 'order' => 4, 'color' => '#2ecc71'],
                ['name' => 'Perdido', 'order' => 5, 'color' => '#e74c3c'],
            ];

            foreach ($defaultStages as $stage) {
                CrmStage::create([
                    'tenant_id' => $tenantId,
                    'crm_pipeline_id' => $this->pipeline->id,
                    'name' => $stage['name'],
                    'order_index' => $stage['order'],
                    'color' => $stage['color']
                ]);
            }
        }

        $this->stages = $this->pipeline->stages()->with(['deals' => function($query) {
            $query->orderBy('created_at', 'desc');
            if (!empty($this->search)) {
                $search = '%' . $this->search . '%';
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', $search)
                      ->orWhereHas('client', function($q2) use ($search) {
                          $q2->where('name', 'like', $search)
                             ->orWhere('phone', 'like', $search);
                      })
                      ->orWhereHas('interactions', function($q3) use ($search) {
                          $q3->where('message', 'like', $search);
                      });
                });
            }
        }])->orderBy('order_index')->get();
    }

    public function updateDealStage($dealId, $newStageId)
    {
        $deal = CrmDeal::where('tenant_id', Auth::user()->tenant_id)->find($dealId);
        if ($deal) {
            $deal->update(['crm_stage_id' => $newStageId]);
            $this->loadPipeline(); // Reload
        }
    }

    public function openDealModal($dealId)
    {
        $this->selectedDeal = CrmDeal::with(['client', 'interactions.user'])->where('tenant_id', Auth::user()->tenant_id)->find($dealId);
        $this->showDealModal = true;
    }

    public function closeDealModal()
    {
        $this->showDealModal = false;
        $this->selectedDeal = null;
    }

    public function getDealColorClass($deal)
    {
        if (!$deal->next_action_date) return 'border-gray-500';
        
        $today = now()->startOfDay();
        $actionDate = \Carbon\Carbon::parse($deal->next_action_date)->startOfDay();

        if ($actionDate->isPast() && !$actionDate->isToday()) {
            return 'border-red-500 border-l-4';
        } elseif ($actionDate->isToday()) {
            return 'border-yellow-500 border-l-4';
        }
        
        return 'border-green-500 border-l-4';
    }

    public function render()
    {
        return view('livewire.crm.crm-board')
            ->extends('layouts.app')
            ->section('content');
    }
}
