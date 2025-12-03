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

<!-- Upload Form -->
<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px; margin-bottom: 30px;">
    <h3 style="color: var(--cor-acento); margin-bottom: 15px;">Upload de XMLs</h3>
    <form action="{{ route('cte-xmls.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div style="display: flex; gap: 15px; align-items: flex-end;">
            <div style="flex: 1;">
                <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Arquivos XML de CT-e</label>
                <input type="file" name="cte_xml_files[]" id="cte_xml_files" multiple accept=".xml,text/xml,application/xml" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                <small style="color: rgba(245, 245, 245, 0.6);">Você pode enviar um ou mais arquivos XML de CT-e</small>
            </div>
            <button type="submit" class="btn-primary" style="padding: 12px 24px;">
                <i class="fas fa-upload"></i> Enviar XMLs
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
    <form method="GET" action="{{ route('cte-xmls.index') }}" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
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
            <input type="date" name="date_from" value="{{ request('date_from') }}" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 5px; font-size: 0.9em;">Data Final</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 5px; font-size: 0.9em;">Buscar</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Número ou chave de acesso" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div style="display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap;">
            <button type="submit" class="btn-secondary" style="padding: 10px 20px;">
                <i class="fas fa-filter"></i> Filtrar
            </button>
            <a href="{{ route('cte-xmls.index') }}" class="btn-secondary" style="padding: 10px 20px;">
                <i class="fas fa-times"></i> Limpar
            </a>
            <a href="{{ route('cte-xmls.export', request()->query()) }}" class="btn-primary" style="padding: 10px 20px; white-space: nowrap;">
                <i class="fas fa-file-excel"></i> Exportar Excel
            </a>
        </div>
    </form>
</div>

<!-- Bulk Actions -->
@if($cteXmls->count() > 0)
<div style="background-color: var(--cor-secundaria); padding: 20px; border-radius: 15px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
    <div style="display: flex; align-items: center; gap: 15px;">
        <label style="color: var(--cor-texto-claro); display: flex; align-items: center; gap: 8px; cursor: pointer;">
            <input type="checkbox" id="select-all-xmls" style="width: 18px; height: 18px; cursor: pointer;">
            <span>Selecionar todos</span>
        </label>
        <span id="selected-count" style="color: var(--cor-acento); font-weight: bold; display: none;">
            <span id="selected-number">0</span> selecionado(s)
        </span>
    </div>
    <div style="display: flex; gap: 10px;">
        <button type="button" id="delete-selected-btn" class="btn-secondary" style="padding: 10px 20px; display: none; background-color: #dc3545; border-color: #dc3545;">
            <i class="fas fa-trash"></i> Excluir Selecionados
        </button>
    </div>
</div>
@endif

<!-- XMLs List -->
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); gap: 20px;">
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

        // Initialize UI
        updateSelectionUI();
    });
</script>
@endpush
@endsection

