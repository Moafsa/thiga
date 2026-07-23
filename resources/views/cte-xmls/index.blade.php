@extends('layouts.app')

@section('title', 'CT-e XMLs - TMS SaaS')
@section('page-title', 'CT-e XMLs')

@push('styles')
@include('shared.styles')
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">CT-e XMLs</h1>
        <h2>Gerencie seus arquivos XML de CT-e</h2>
    </div>
</div>

<!-- CT-e Statistics Summary Bar -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 25px;">
    <div style="background: linear-gradient(135deg, var(--cor-secundaria) 0%, rgba(var(--cor-acento-rgb), 0.1) 100%); border: 1px solid rgba(var(--cor-acento-rgb), 0.3); padding: 20px; border-radius: 12px; display: flex; align-items: center; justify-content: space-between;">
        <div>
            <span style="color: rgba(245,245,245,0.7); font-size: 0.85em; display: block; margin-bottom: 5px;">Total de CT-es Cadastradas</span>
            <strong style="color: var(--cor-acento); font-size: 1.8em;">{{ number_format($totalCtes ?? 0, 0, ',', '.') }}</strong>
        </div>
        <div style="width: 48px; height: 48px; border-radius: 10px; background: rgba(var(--cor-acento-rgb), 0.2); display: flex; align-items: center; justify-content: center; color: var(--cor-acento); font-size: 1.4em;">
            <i class="fas fa-file-code"></i>
        </div>
    </div>

    <div style="background: linear-gradient(135deg, var(--cor-secundaria) 0%, rgba(76, 175, 80, 0.1) 100%); border: 1px solid rgba(76, 175, 80, 0.3); padding: 20px; border-radius: 12px; display: flex; align-items: center; justify-content: space-between;">
        <div>
            <span style="color: rgba(245,245,245,0.7); font-size: 0.85em; display: block; margin-bottom: 5px;">CT-es Usadas</span>
            <strong style="color: #4caf50; font-size: 1.8em;">{{ number_format($usedCtes ?? 0, 0, ',', '.') }}</strong>
        </div>
        <div style="width: 48px; height: 48px; border-radius: 10px; background: rgba(76, 175, 80, 0.2); display: flex; align-items: center; justify-content: center; color: #4caf50; font-size: 1.4em;">
            <i class="fas fa-check-circle"></i>
        </div>
    </div>

    <div style="background: linear-gradient(135deg, var(--cor-secundaria) 0%, rgba(255, 152, 0, 0.1) 100%); border: 1px solid rgba(255, 152, 0, 0.3); padding: 20px; border-radius: 12px; display: flex; align-items: center; justify-content: space-between;">
        <div>
            <span style="color: rgba(245,245,245,0.7); font-size: 0.85em; display: block; margin-bottom: 5px;">CT-es a Usar (Disponíveis)</span>
            <strong style="color: #ff9800; font-size: 1.8em;">{{ number_format($unusedCtes ?? 0, 0, ',', '.') }}</strong>
        </div>
        <div style="width: 48px; height: 48px; border-radius: 10px; background: rgba(255, 152, 0, 0.2); display: flex; align-items: center; justify-content: center; color: #ff9800; font-size: 1.4em;">
            <i class="fas fa-clock"></i>
        </div>
    </div>
</div>

@if(session('warning'))
    <div style="background-color: rgba(255, 193, 7, 0.15); border: 1px solid #ffc107; color: #ffe082; padding: 15px; border-radius: 10px; margin-bottom: 20px; font-weight: 500;">
        <i class="fas fa-exclamation-triangle" style="margin-right: 8px;"></i> {{ session('warning') }}
    </div>
@endif

<!-- Upload Form -->
<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px; margin-bottom: 30px;">
    <h3 style="color: var(--cor-acento); margin-bottom: 15px;">Upload de XMLs ou pacotes ZIP</h3>
    <form id="xml-upload-form" action="{{ route('cte-xmls.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div style="display: flex; gap: 15px; align-items: flex-end;">
            <div style="flex: 1;">
                <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Arquivos XML ou arquivo .ZIP de CT-e</label>
                <input type="file" name="cte_xml_files[]" id="cte_xml_files" multiple accept=".xml,.zip,text/xml,application/xml,application/zip,application/x-zip-compressed" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                <small style="color: rgba(245, 245, 245, 0.6);">Você pode enviar arquivos XML de CT-e individuais ou um arquivo <strong>.ZIP</strong> contendo múltiplos XMLs.</small>
            </div>
            <button type="submit" class="btn-primary" style="padding: 12px 24px;">
                <i class="fas fa-upload"></i> Enviar XMLs / ZIP
            </button>
        </div>
        @error('cte_xml_files')
            <div style="color: #ff6b6b; margin-top: 10px;">{{ $message }}</div>
        @enderror
        <div id="xml-files-list" style="margin-top: 10px;"></div>
    </form>
</div>

<!-- Filters -->
<div style="background-color: var(--cor-secundaria); padding: 20px; border-radius: 15px; margin-bottom: 20px;">
    <form method="GET" action="{{ route('cte-xmls.index') }}" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px;">
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 5px; font-size: 0.9em;">Status</label>
            <select name="status" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                <option value="">Todos</option>
                <option value="unused" {{ request('status') === 'unused' ? 'selected' : '' }}>Não Usados</option>
                <option value="used" {{ request('status') === 'used' ? 'selected' : '' }}>Usados</option>
            </select>
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 5px; font-size: 0.9em;">Data Inicial</label>
            <input type="date" name="date_from" id="filter_date_from" value="{{ request('date_from') }}" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 5px; font-size: 0.9em;">Data Final</label>
            <input type="date" name="date_to" id="filter_date_to" value="{{ request('date_to') }}" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 5px; font-size: 0.9em;">Buscar</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Número ou chave de acesso" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div style="display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap; grid-column: span 2;">
            <button type="submit" class="btn-secondary" style="padding: 10px 20px;">
                <i class="fas fa-filter"></i> Filtrar
            </button>
            <a href="{{ route('cte-xmls.index') }}" class="btn-secondary" style="padding: 10px 20px;">
                <i class="fas fa-times"></i> Limpar
            </a>
            <a href="{{ route('cte-xmls.export', request()->query()) }}" class="btn-primary" style="padding: 10px 20px; white-space: nowrap;">
                <i class="fas fa-file-excel"></i> Exportar Excel
            </a>
            <button type="button" id="btn-ai-analysis" class="btn-secondary" style="padding: 10px 20px; white-space: nowrap; background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); border: none; color: white;">
                <i class="fas fa-robot"></i> Análise Geral com IA
            </button>
        </div>
    </form>
</div>

<!-- Bulk Actions -->
@if($cteXmls->count() > 0)
<div style="background-color: var(--cor-secundaria); padding: 15px 20px; border-radius: 15px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
    <div style="display: flex; align-items: center; gap: 15px;">
        <label style="color: var(--cor-texto-claro); display: flex; align-items: center; gap: 8px; cursor: pointer;">
            <input type="checkbox" id="select-all-xmls" style="width: 18px; height: 18px; cursor: pointer;">
            <span>Selecionar todos</span>
        </label>
        <span id="selected-count" style="color: var(--cor-acento); font-weight: bold; display: none;">
            <span id="selected-number">0</span> selecionado(s)
        </span>
    </div>
    <div style="display: flex; gap: 12px; align-items: center;">
        <!-- Toggle Exibição: Cards vs Lista -->
        <div style="background: var(--cor-principal); border-radius: 8px; padding: 4px; display: flex; gap: 4px; border: 1px solid rgba(255,255,255,0.15);">
            <button type="button" id="btn-view-cards" title="Exibir em Cards" style="padding: 6px 14px; border-radius: 6px; border: none; background: var(--cor-acento); color: var(--cor-principal); font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 6px; font-size: 0.85em; transition: all 0.2s;">
                <i class="fas fa-th-large"></i> Cards
            </button>
            <button type="button" id="btn-view-list" title="Exibir em Lista" style="padding: 6px 14px; border-radius: 6px; border: none; background: transparent; color: var(--cor-texto-claro); font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; font-size: 0.85em; transition: all 0.2s;">
                <i class="fas fa-list"></i> Lista
            </button>
        </div>

        <button type="button" id="delete-selected-btn" class="btn-secondary" style="padding: 10px 20px; display: none; background-color: #dc3545; border-color: #dc3545;">
            <i class="fas fa-trash"></i> Excluir Selecionados
        </button>
    </div>
</div>
@endif

<!-- XMLs Cards Container -->
<div id="xml-cards-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); gap: 20px;">
    @forelse($cteXmls as $cteXml)
        <div class="xml-card" style="background-color: var(--cor-secundaria); padding: 25px; border-radius: 15px; box-shadow: 0 4px 8px rgba(0,0,0,0.3);">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                <div style="flex: 1;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                        <input type="checkbox" class="xml-checkbox" value="{{ $cteXml->id }}" style="width: 18px; height: 18px; cursor: pointer; flex-shrink: 0;">
                        <h3 style="color: var(--cor-texto-claro); font-size: 1.2em; margin: 0;">
                            CT-e Nº {{ $cteXml->cte_number }}
                        </h3>
                    </div>
                    @if($cteXml->access_key)
                        <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.85em; word-break: break-all; margin-left: 28px;">
                            Chave: {{ $cteXml->access_key }}
                        </p>
                    @endif
                    <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin-top: 5px; margin-left: 28px;">
                        Enviado em: {{ $cteXml->created_at->format('d/m/Y H:i') }}
                    </p>
                    @if($cteXml->is_used && $cteXml->used_at)
                        <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin-left: 28px;">
                            Usado em: {{ $cteXml->used_at->format('d/m/Y H:i') }}
                        </p>
                        @if($cteXml->route)
                            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin-left: 28px;">
                                Rota: <a href="{{ route('routes.show', $cteXml->route) }}" style="color: var(--cor-acento);">{{ $cteXml->route->name }}</a>
                            </p>
                        @endif
                    @endif
                </div>
                <div style="display: flex; gap: 10px; flex-shrink: 0;">
                    <a href="{{ route('cte-xmls.download', $cteXml) }}" class="action-btn" title="Download">
                        <i class="fas fa-download"></i>
                    </a>
                    <form action="{{ route('cte-xmls.destroy', $cteXml) }}" method="POST" style="display: inline;" class="delete-xml-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="action-btn" title="Excluir" style="background-color: #dc3545; border-color: #dc3545;" onclick="return confirm('Tem certeza que deseja excluir este XML?');">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255, 255, 255, 0.1);">
                @if($cteXml->is_used)
                    <span class="status-badge" style="background-color: rgba(76, 175, 80, 0.2); color: #4caf50; border: 1px solid rgba(76, 175, 80, 0.3);">
                        <i class="fas fa-check-circle"></i> Usado
                    </span>
                @else
                    <span class="status-badge" style="background-color: rgba(255, 152, 0, 0.2); color: #ff9800; border: 1px solid rgba(255, 152, 0, 0.3);">
                        <i class="fas fa-clock"></i> Não Usado
                    </span>
                @endif
            </div>
        </div>
    @empty
        <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
            <i class="fas fa-file-code" style="font-size: 5em; color: rgba(245, 245, 245, 0.3); margin-bottom: 20px;"></i>
            <h3 style="color: var(--cor-texto-claro); font-size: 1.5em; margin-bottom: 10px;">Nenhum XML encontrado</h3>
            <p style="color: rgba(245, 245, 245, 0.7);">Faça upload de arquivos XML de CT-e acima</p>
        </div>
    @endforelse
</div>

<!-- XMLs List/Table Container -->
<div id="xml-list-container" style="display: none; background-color: var(--cor-secundaria); border-radius: 15px; padding: 20px; overflow-x: auto; box-shadow: 0 4px 8px rgba(0,0,0,0.3);">
    @if($cteXmls->count() > 0)
    <table style="width: 100%; border-collapse: collapse; text-align: left; color: var(--cor-texto-claro);">
        <thead>
            <tr style="border-bottom: 1px solid rgba(255,255,255,0.1); color: var(--cor-acento); font-size: 0.9em; text-transform: uppercase; letter-spacing: 0.5px;">
                <th style="padding: 12px; width: 40px;"></th>
                <th style="padding: 12px;">Número CT-e</th>
                <th style="padding: 12px;">Chave de Acesso</th>
                <th style="padding: 12px;">Data de Envio</th>
                <th style="padding: 12px;">Status</th>
                <th style="padding: 12px;">Rota Vinculada</th>
                <th style="padding: 12px; text-align: right;">Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cteXmls as $cteXml)
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 0.9em;">
                    <td style="padding: 12px;">
                        <input type="checkbox" class="xml-checkbox" value="{{ $cteXml->id }}" style="width: 18px; height: 18px; cursor: pointer;">
                    </td>
                    <td style="padding: 12px; font-weight: 700;">
                        CT-e Nº {{ $cteXml->cte_number }}
                    </td>
                    <td style="padding: 12px; font-family: monospace; font-size: 0.85em; opacity: 0.8; word-break: break-all;">
                        {{ $cteXml->access_key ?: 'N/A' }}
                    </td>
                    <td style="padding: 12px;">
                        {{ $cteXml->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td style="padding: 12px;">
                        @if($cteXml->is_used)
                            <span class="status-badge" style="background-color: rgba(76, 175, 80, 0.2); color: #4caf50; padding: 4px 10px; border-radius: 12px; font-size: 0.8em; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                <i class="fas fa-check-circle"></i> Usado
                            </span>
                        @else
                            <span class="status-badge" style="background-color: rgba(255, 152, 0, 0.2); color: #ff9800; padding: 4px 10px; border-radius: 12px; font-size: 0.8em; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                <i class="fas fa-clock"></i> Não Usado
                            </span>
                        @endif
                    </td>
                    <td style="padding: 12px;">
                        @if($cteXml->is_used && $cteXml->route)
                            <a href="{{ route('routes.show', $cteXml->route) }}" style="color: var(--cor-acento); font-weight: 600;">{{ $cteXml->route->name }}</a>
                        @else
                            <span style="opacity: 0.4;">-</span>
                        @endif
                    </td>
                    <td style="padding: 12px; text-align: right;">
                        <div style="display: flex; gap: 8px; justify-content: flex-end;">
                            <a href="{{ route('cte-xmls.download', $cteXml) }}" class="action-btn" title="Download">
                                <i class="fas fa-download"></i>
                            </a>
                            <form action="{{ route('cte-xmls.destroy', $cteXml) }}" method="POST" style="display: inline;" class="delete-xml-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn" title="Excluir" style="background-color: #dc3545; border-color: #dc3545;" onclick="return confirm('Tem certeza que deseja excluir este XML?');">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @else
        <div style="text-align: center; padding: 60px 20px;">
            <i class="fas fa-file-code" style="font-size: 5em; color: rgba(245, 245, 245, 0.3); margin-bottom: 20px;"></i>
            <h3 style="color: var(--cor-texto-claro); font-size: 1.5em; margin-bottom: 10px;">Nenhum XML encontrado</h3>
            <p style="color: rgba(245, 245, 245, 0.7);">Faça upload de arquivos XML de CT-e acima</p>
        </div>
    @endif
</div>

<div style="margin-top: 30px;">
    {{ $cteXmls->links() }}
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const xmlFilesInput = document.getElementById('cte_xml_files');
        const xmlFilesList = document.getElementById('xml-files-list');
        
        function updateXmlFilesList() {
            const files = xmlFilesInput.files;
            if (files.length === 0) {
                xmlFilesList.innerHTML = '';
                return;
            }
            
            let html = '<div style="margin-top: 10px; padding: 10px; background: var(--cor-principal); border-radius: 5px;">';
            html += '<strong style="color: var(--cor-texto-claro);">Arquivos selecionados:</strong><ul style="margin: 5px 0 0 20px; color: var(--cor-texto-claro);">';
            for (let i = 0; i < files.length; i++) {
                html += '<li>' + files[i].name + ' (' + (files[i].size / 1024).toFixed(2) + ' KB)</li>';
            }
            html += '</ul></div>';
            xmlFilesList.innerHTML = html;
        }
        
        xmlFilesInput.addEventListener('change', updateXmlFilesList);

        // Cards vs List View Mode Toggle
        const btnCards = document.getElementById('btn-view-cards');
        const btnList = document.getElementById('btn-view-list');
        const cardsContainer = document.getElementById('xml-cards-container');
        const listContainer = document.getElementById('xml-list-container');

        function setViewMode(mode) {
            if (!cardsContainer || !listContainer) return;
            
            if (mode === 'list') {
                cardsContainer.style.display = 'none';
                listContainer.style.display = 'block';
                if (btnList && btnCards) {
                    btnList.style.background = 'var(--cor-acento)';
                    btnList.style.color = 'var(--cor-principal)';
                    btnList.style.fontWeight = '700';
                    btnCards.style.background = 'transparent';
                    btnCards.style.color = 'var(--cor-texto-claro)';
                    btnCards.style.fontWeight = '600';
                }
            } else {
                cardsContainer.style.display = 'grid';
                listContainer.style.display = 'none';
                if (btnCards && btnList) {
                    btnCards.style.background = 'var(--cor-acento)';
                    btnCards.style.color = 'var(--cor-principal)';
                    btnCards.style.fontWeight = '700';
                    btnList.style.background = 'transparent';
                    btnList.style.color = 'var(--cor-texto-claro)';
                    btnList.style.fontWeight = '600';
                }
            }
            localStorage.setItem('cte_view_mode', mode);
        }

        if (btnCards && btnList) {
            btnCards.addEventListener('click', () => setViewMode('cards'));
            btnList.addEventListener('click', () => setViewMode('list'));
            const savedMode = localStorage.getItem('cte_view_mode') || 'cards';
            setViewMode(savedMode);
        }

        // Bulk selection functionality
        const selectAllCheckbox = document.getElementById('select-all-xmls');
        const xmlCheckboxes = document.querySelectorAll('.xml-checkbox');
        const selectedCountSpan = document.getElementById('selected-count');
        const selectedNumberSpan = document.getElementById('selected-number');
        const deleteSelectedBtn = document.getElementById('delete-selected-btn');

        function updateSelectionUI() {
            const checkedBoxes = document.querySelectorAll('.xml-checkbox:checked');
            const count = checkedBoxes.length;
            
            if (count > 0) {
                selectedCountSpan.style.display = 'inline';
                selectedNumberSpan.textContent = count;
                deleteSelectedBtn.style.display = 'inline-block';
            } else {
                selectedCountSpan.style.display = 'none';
                deleteSelectedBtn.style.display = 'none';
            }

            // Update select all checkbox state
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = count === xmlCheckboxes.length && xmlCheckboxes.length > 0;
                selectAllCheckbox.indeterminate = count > 0 && count < xmlCheckboxes.length;
            }
        }

        // Select all checkbox
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                xmlCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateSelectionUI();
            });
        }

        // Individual checkboxes
        xmlCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateSelectionUI);
        });

        // Delete selected button
        if (deleteSelectedBtn) {
            deleteSelectedBtn.addEventListener('click', function() {
                const checkedBoxes = document.querySelectorAll('.xml-checkbox:checked');
                const ids = Array.from(checkedBoxes).map(cb => cb.value);
                
                if (ids.length === 0) {
                    alert('Nenhum XML selecionado.');
                    return;
                }

                if (!confirm(`Tem certeza que deseja excluir ${ids.length} XML(s) selecionado(s)?`)) {
                    return;
                }

                // Create form and submit
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("cte-xmls.destroy-multiple") }}';
                
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                form.appendChild(csrfInput);

                ids.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'xml_ids[]';
                    input.value = id;
                    form.appendChild(input);
                });

                document.body.appendChild(form);
                form.submit();
            });
        }

        // AI Analysis Modal Handler
        const btnAiAnalysis = document.getElementById('btn-ai-analysis');
        const modalAi = document.getElementById('modal-ai-analysis');
        const modalAiClose = document.getElementById('modal-ai-close');
        const aiContent = document.getElementById('ai-analysis-content');

        if (btnAiAnalysis && modalAi) {
            btnAiAnalysis.addEventListener('click', function() {
                modalAi.style.display = 'flex';
                aiContent.innerHTML = '<div style="text-align: center; padding: 40px; color: var(--cor-acento);"><i class="fas fa-spinner fa-spin fa-2x"></i><p style="margin-top: 15px;">Gerando análise de inteligência fiscal dos CT-es...</p></div>';

                const dateFrom = document.getElementById('filter_date_from')?.value || '';
                const dateTo = document.getElementById('filter_date_to')?.value || '';

                fetch('{{ route("cte-xmls.ai-analysis") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ date_from: dateFrom, date_to: dateTo })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.analysis) {
                        let formattedText = data.analysis
                            .replace(/### (.*?)\n/g, '<h4 style="color: var(--cor-acento); margin-top: 15px; margin-bottom: 8px;">$1</h4>')
                            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                            .replace(/- (.*?)\n/g, '<li style="margin-bottom: 4px;">$1</li>');
                        aiContent.innerHTML = '<div style="line-height: 1.6; color: var(--cor-texto-claro);">' + formattedText + '</div>';
                    } else {
                        aiContent.innerHTML = '<p style="color: #ef4444;">Não foi possível gerar a análise.</p>';
                    }
                })
                .catch(err => {
                    aiContent.innerHTML = '<p style="color: #ef4444;">Erro na requisição da análise de IA.</p>';
                });
            });

            if (modalAiClose) {
                modalAiClose.addEventListener('click', function() {
                    modalAi.style.display = 'none';
                });
            }
        }

        // Validate form submit: if > 20 files and none is zip, alert and prevent.
        const uploadForm = document.getElementById('xml-upload-form');
        if (uploadForm) {
            uploadForm.addEventListener('submit', function(e) {
                const files = xmlFilesInput.files;
                let hasZip = false;
                let xmlCount = 0;
                for (let i = 0; i < files.length; i++) {
                    if (files[i].name.toLowerCase().endsWith('.zip')) {
                        hasZip = true;
                    } else {
                        xmlCount++;
                    }
                }
                if (!hasZip && xmlCount > 20) {
                    e.preventDefault();
                    alert('⚠️ MUITOS ARQUIVOS SELECIONADOS (' + xmlCount + ' XMLs)\n\nPara enviar mais de 20 arquivos de uma só vez, por favor compacte todos os XMLs em um único arquivo .ZIP antes de enviar.');
                    return false;
                }
                
                // Show loading overlay
                const overlay = document.getElementById('xml-processing-overlay');
                if (overlay) {
                    overlay.style.display = 'flex';
                }
            });
        }

        // Initialize UI
        updateSelectionUI();
    });
</script>

<!-- Modal Análise por IA -->
<div id="modal-ai-analysis" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: var(--cor-secundaria); border: 1px solid rgba(255,255,255,0.15); border-radius: 16px; width: 100%; max-width: 700px; max-height: 85vh; overflow-y: auto; padding: 30px; box-shadow: 0 20px 50px rgba(0,0,0,0.5);">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px; margin-bottom: 20px;">
            <h3 style="color: var(--cor-acento); display: flex; align-items: center; gap: 10px; margin: 0;">
                <i class="fas fa-robot"></i> Análise Geral de CT-es por IA
            </h3>
            <button type="button" id="modal-ai-close" style="background: none; border: none; color: #94a3b8; font-size: 20px; cursor: pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="ai-analysis-content"></div>
    </div>
</div>

<!-- Modal Overlay de Processamento de XML/ZIP -->
<div id="xml-processing-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.85); z-index: 99999; align-items: center; justify-content: center; backdrop-filter: blur(5px);">
    <div style="background: var(--cor-secundaria); border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 16px; padding: 40px; text-align: center; max-width: 500px; width: 90%; box-shadow: 0 20px 50px rgba(0,0,0,0.6);">
        <div style="margin-bottom: 20px;">
            <i class="fas fa-file-archive fa-3x" style="color: var(--cor-acento); animation: pulse-icon 1.5s infinite;"></i>
        </div>
        <h3 id="xml-processing-title" style="color: var(--cor-texto-claro); font-size: 1.3em; margin-bottom: 10px;">Descompactando e Processando Arquivos .ZIP...</h3>
        <p id="xml-processing-subtitle" style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin-bottom: 25px;">Aguarde enquanto os CT-es são lidos e integrados ao sistema.</p>
        
        <div style="width: 100%; background: rgba(255,255,255,0.1); height: 16px; border-radius: 8px; overflow: hidden; margin-bottom: 15px;">
            <div id="xml-progress-bar" style="width: 100%; height: 100%; background: linear-gradient(45deg, var(--cor-acento) 25%, #f59e0b 25%, #f59e0b 50%, var(--cor-acento) 50%, var(--cor-acento) 75%, #f59e0b 75%, #f59e0b 100%); background-size: 40px 40px; animation: progress-stripes 1s linear infinite; border-radius: 8px;"></div>
        </div>
    </div>
</div>

<style>
@keyframes progress-stripes {
    0% { background-position: 0 0; }
    100% { background-position: 40px 0; }
}
@keyframes pulse-icon {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.1); opacity: 0.8; }
    100% { transform: scale(1); opacity: 1; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const xmlInput = document.getElementById('cte_xml_files');
    if (xmlInput) {
        const form = xmlInput.closest('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const files = xmlInput.files;
                if (!files || files.length === 0) return;

                let unzippedCount = 0;
                let hasZip = false;
                for (let i = 0; i < files.length; i++) {
                    const ext = files[i].name.split('.').pop().toLowerCase();
                    if (ext === 'zip') {
                        hasZip = true;
                    } else {
                        unzippedCount++;
                    }
                }

                if (unzippedCount > 20 && !hasZip) {
                    e.preventDefault();
                    alert('⚠️ Para enviar mais de 20 arquivos XML avulsos, por favor compacte-os em um único arquivo .ZIP para garantir maior velocidade no processamento.');
                    return false;
                }

                const overlay = document.getElementById('xml-processing-overlay');
                if (overlay) {
                    if (hasZip) {
                        document.getElementById('xml-processing-title').textContent = 'Descompactando e Processando Arquivos .ZIP...';
                    } else {
                        document.getElementById('xml-processing-title').textContent = 'Processando Arquivos XML...';
                    }
                    overlay.style.display = 'flex';
                }
            });
        }
    }
});
</script>
@endpush
@endsection

