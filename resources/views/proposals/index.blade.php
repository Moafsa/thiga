@extends('layouts.app')

@section('title', 'Propostas - TMS SaaS')
@section('page-title', 'Propostas')

@push('styles')
    @include('shared.styles')
    <style>
        .status-pending {
            background-color: rgba(255, 193, 7, 0.2);
            color: #ffc107;
        }

        .status-sent {
            background-color: rgba(33, 150, 243, 0.2);
            color: #2196f3;
        }

        .status-accepted {
            background-color: rgba(76, 175, 80, 0.2);
            color: #4caf50;
        }

        .status-rejected {
            background-color: rgba(244, 67, 54, 0.2);
            color: #f44336;
        }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <div class="page-header-text">
            <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Propostas</h1>
            <h2>Gerencie suas propostas comerciais</h2>
        </div>
        <a href="{{ route('proposals.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i>
            Nova Proposta
        </a>
    </div>

    <!-- Filters -->
    <div class="card">
        <form method="GET" action="{{ route('proposals.index') }}">
            <div class="filters-grid">
                <div class="filter-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="">Todos</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Rascunho</option>
                        <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Enviada</option>
                        <option value="negotiating" {{ request('status') === 'negotiating' ? 'selected' : '' }}>Em Negociação
                        </option>
                        <option value="accepted" {{ request('status') === 'accepted' ? 'selected' : '' }}>Aceita</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejeitada</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expirada</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Vendedor</label>
                    <select name="salesperson_id">
                        <option value="">Todos</option>
                        @foreach($salespeople as $salesperson)
                            <option value="{{ $salesperson->id }}" {{ request('salesperson_id') == $salesperson->id ? 'selected' : '' }}>
                                {{ $salesperson->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 15px;">
                <a href="{{ route('proposals.index') }}" class="btn-secondary">
                    Limpar
                </a>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-search"></i>
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Proposals Table -->
    <div class="table-card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Cliente</th>
                        <th>Vendedor</th>
                        <th>Valor Total</th>
                        <th>Data</th>
                        <th>Status</th>
                        <th>Coleta</th>
                        <th style="text-align: center;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($proposals as $proposal)
                        <tr>
                            <td>
                                <span style="font-family: monospace; font-weight: 600;">#{{ $proposal->id }}</span>
                            </td>
                            <td>
                                <div style="font-weight: 600;">{{ $proposal->client->name ?? 'N/A' }}</div>
                            </td>
                            <td>
                                <div>{{ $proposal->salesperson->name ?? 'N/A' }}</div>
                            </td>
                            <td style="font-weight: 600;">
                                R$ {{ number_format($proposal->final_value ?? 0, 2, ',', '.') }}
                            </td>
                            <td>
                                {{ $proposal->created_at->format('d/m/Y') }}
                            </td>
                            <td>
                                <span class="status-badge status-{{ $proposal->status }}">
                                    {{ $proposal->status_label }}
                                </span>
                            </td>
                            <td>
                                @if($proposal->collection_requested)
                                    <span style="color: #ff9800; font-weight: 600;">
                                        <i class="fas fa-truck"></i> Solicitada
                                    </span>
                                @else
                                    <span style="color: #999;">-</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <div class="action-buttons" style="justify-content: center;">
                                    <a href="{{ route('proposals.show', $proposal) }}" class="action-btn" title="Ver detalhes">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('proposals.edit', $proposal) }}" class="action-btn" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-state">
                                <i class="fas fa-file-contract"></i>
                                <h3>Nenhuma proposta encontrada</h3>
                                <p>Comece criando sua primeira proposta</p>
                                <a href="{{ route('proposals.create') }}" class="btn-primary">
                                    <i class="fas fa-plus"></i>
                                    Nova Proposta
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($proposals->hasPages())
            <div style="padding: 20px; border-top: 1px solid rgba(255, 255, 255, 0.1);">
                {{ $proposals->links() }}
            </div>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check mr-2"></i>
            {{ session('success') }}
        </div>
    @endif

    @push('scripts')
        <script>
            setTimeout(() => {
                const messages = document.querySelectorAll('.alert');
                messages.forEach(msg => msg.remove());
            }, 5000);
        </script>
    @endpush
    <!-- Embed Modal -->
    <div id="embedModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Widget da Calculadora de Frete</h3>
                <button onclick="document.getElementById('embedModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-500">
                    <span class="text-2xl">&times;</span>
                </button>
            </div>

            <div class="space-y-4">
                <p class="text-sm text-gray-600">Copie o código abaixo e cole no seu site para exibir a calculadora de
                    fretes.</p>

                <div class="bg-gray-100 p-4 rounded text-sm font-mono break-all relative group">
                    <code
                        id="embedCode">&lt;iframe src="{{ route('public.calculator.show', Auth::user()->tenant->domain ?? 'seu-dominio') }}" width="100%" height="550" frameborder="0" style="border:0; max-width: 400px; margin: 0 auto; display: block;"&gt;&lt;/iframe&gt;</code>
                    <button
                        onclick="navigator.clipboard.writeText(document.getElementById('embedCode').innerText); alert('Copiado!')"
                        class="absolute top-2 right-2 bg-white px-2 py-1 text-xs border rounded hover:bg-gray-50 text-indigo-600">
                        Copiar
                    </button>
                </div>

                <div class="mt-4">
                    <h4 class="text-sm font-medium text-gray-900 mb-2">Visualização:</h4>
                    <iframe src="{{ route('public.calculator.show', Auth::user()->tenant->domain ?? 'seu-dominio') }}"
                        width="100%" height="400" frameborder="0" class="border rounded shadow-sm mx-auto"
                        style="max-width: 400px;"></iframe>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button onclick="document.getElementById('embedModal').classList.add('hidden')"
                    class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300">
                    Fechar
                </button>
            </div>
        </div>
    </div>
@endsection