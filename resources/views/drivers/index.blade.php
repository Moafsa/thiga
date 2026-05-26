@extends('layouts.app')

@section('title', 'Motoristas - TMS SaaS')
@section('page-title', 'Motoristas')

@push('styles')
@include('shared.styles')
<style>
    .drivers-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 20px;
    }

    .driver-card {
        background-color: var(--cor-secundaria);
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        transition: transform 0.3s ease;
    }

    .driver-card:hover {
        transform: translateY(-5px);
    }

    .action-buttons {
        display: flex;
        gap: 10px;
    }

    .action-btn {
        color: var(--cor-texto-claro);
        opacity: 0.7;
        transition: opacity 0.3s ease;
        text-decoration: none;
        font-size: 1.1em;
        background: none;
        border: none;
        padding: 0;
        cursor: pointer;
    }

    .action-btn:hover {
        opacity: 1;
        color: var(--cor-acento);
    }

    .action-btn.delete-btn {
        color: #f44336;
    }

    .action-btn.delete-btn:hover {
        opacity: 1;
        color: #d32f2f;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Motoristas</h1>
        <h2>Gerencie seus motoristas</h2>
    </div>
    <a href="{{ route('drivers.create') }}" class="btn-primary">
        <i class="fas fa-plus"></i>
        Novo Motorista
    </a>
</div>

<!-- Onboarding Rápido: Importar Motoristas via CSV -->
<div class="filters-section" x-data="{ open: false }" style="margin-bottom: 25px;">
    <div style="display: flex; justify-content: space-between; align-items: center; cursor: pointer;" x-on:click="open = !open">
        <h3 style="color: var(--cor-acento); font-size: 1.1em; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-file-import"></i> Onboarding Rápido: Importar Motoristas via CSV
        </h3>
        <span style="color: var(--cor-acento);"><i class="fas" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i></span>
    </div>
    
    <div x-show="open" x-transition style="margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 20px;">
        <form method="POST" action="{{ route('drivers.import') }}" enctype="multipart/form-data" style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; align-items: flex-start;">
            @csrf
            <div>
                <label style="color: var(--cor-texto-claro); font-size: 0.9em; font-weight: 600; display: block; margin-bottom: 8px;">Selecione o arquivo CSV *</label>
                <input type="file" name="csv_file" accept=".csv,.txt" required style="width: 100%; padding: 10px; border: 1px dashed rgba(255,255,255,0.2); border-radius: 8px; background: rgba(255,255,255,0.03); color: var(--cor-texto-claro);">
                
                <p style="font-size: 0.8em; opacity: 0.6; margin-top: 10px; line-height: 1.5; color: var(--cor-texto-claro);">
                    💡 <strong>Colunas recomendadas no arquivo CSV:</strong><br>
                    <code>name</code> (Nome do Motorista) *, <code>phone</code> (Telefone) *, <code>email</code>, <code>document</code> (CPF), <code>cnh_number</code> (Nº CNH), <code>cnh_category</code> (Categoria), <code>cnh_expiry_date</code> (Validade CNH), <code>vehicle_plate</code> (Placa), <code>vehicle_model</code> (Modelo), <code>vehicle_color</code> (Cor).<br>
                    <span style="opacity: 0.8;">* Os campos <code>name</code> and <code>phone</code> são obrigatórios. O sistema criará a conta de login e atribuições automaticamente.</span>
                </p>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 15px; align-self: stretch; justify-content: space-between;">
                <div style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 8px; padding: 12px; font-size: 0.85em; opacity: 0.85; color: var(--cor-texto-claro);">
                    <i class="fas fa-info-circle text-accent"></i> Separadores suportados: Vírgula (<code>,</code>) ou Ponto e Vírgula (<code>;</code>). O arquivo deve possuir cabeçalhos na primeira linha.
                </div>
                
                <button type="submit" class="btn-primary" style="width: 100%; padding: 12px;">
                    <i class="fas fa-upload"></i> Processar Importação
                </button>
            </div>
        </form>
    </div>
</div>

@if(session('import_errors'))
<div class="card" style="margin-bottom: 25px; border: 1px solid rgba(244, 67, 54, 0.3); background-color: rgba(244, 67, 54, 0.03); padding: 20px; border-radius: 12px;">
    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px; border-bottom: 1px solid rgba(244, 67, 54, 0.1); padding-bottom: 10px;">
        <i class="fas fa-exclamation-circle" style="color: #ff4d4f; font-size: 1.2rem;"></i>
        <h3 style="color: #ff4d4f; margin: 0; font-size: 1.1rem; font-weight: 600;">Detalhes dos Erros de Importação</h3>
    </div>
    <ul style="margin: 0; padding-left: 20px; color: #ff9f9f; line-height: 1.6; font-size: 0.9em;">
        @foreach(session('import_errors') as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

@if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check mr-2"></i>
        {{ session('success') }}
    </div>
@endif

@if(session('warning'))
    <div class="alert" style="background-color: rgba(255, 152, 0, 0.9); color: white;">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        {{ session('warning') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-error">
        <i class="fas fa-times-circle mr-2"></i>
        {{ session('error') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-error">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<div class="drivers-grid">
    @forelse($drivers as $driver)
        <div class="driver-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div>
                    <h3 style="color: var(--cor-texto-claro); font-size: 1.3em; margin-bottom: 5px;">{{ $driver->name }}</h3>
                    @if($driver->vehicle_plate)
                        <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Veículo: {{ $driver->vehicle_plate }}</p>
                    @endif
                </div>
                <div class="action-buttons">
                    <a href="{{ route('drivers.show', $driver) }}" class="action-btn" title="Ver">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route('drivers.edit', $driver) }}" class="action-btn" title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form action="{{ route('drivers.destroy', $driver) }}" method="POST" style="display: inline;" 
                          onsubmit="return confirm('Tem certeza que deseja excluir o motorista {{ $driver->name }}? Esta ação não pode ser desfeita.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="action-btn delete-btn" title="Excluir">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255, 255, 255, 0.1);">
                <span class="status-badge" style="background-color: {{ $driver->is_active ? 'rgba(76, 175, 80, 0.2)' : 'rgba(244, 67, 54, 0.2)' }}; color: {{ $driver->is_active ? '#4caf50' : '#f44336' }};">
                    {{ $driver->is_active ? 'Ativo' : 'Inativo' }}
                </span>
            </div>
        </div>
    @empty
        <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
            <i class="fas fa-user" style="font-size: 5em; color: rgba(245, 245, 245, 0.3); margin-bottom: 20px;"></i>
            <h3 style="color: var(--cor-texto-claro); font-size: 1.5em; margin-bottom: 10px;">Nenhum motorista encontrado</h3>
            <a href="{{ route('drivers.create') }}" class="btn-primary">
                <i class="fas fa-plus"></i>
                Novo Motorista
            </a>
        </div>
    @endforelse
</div>

<div style="margin-top: 30px;">
    {{ $drivers->links() }}
</div>

@push('scripts')
<script>
    setTimeout(() => {
        const messages = document.querySelectorAll('.alert');
        messages.forEach(msg => msg.remove());
    }, 5000);
</script>
@endpush
@endsection

















