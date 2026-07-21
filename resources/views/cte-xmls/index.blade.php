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

@if(session('warning'))
    <div style="background-color: rgba(255, 193, 7, 0.15); border: 1px solid #ffc107; color: #ffe082; padding: 15px; border-radius: 10px; margin-bottom: 20px; font-weight: 500;">
        <i class="fas fa-exclamation-triangle" style="margin-right: 8px;"></i> {{ session('warning') }}
    </div>
@endif

<!-- Upload Form -->
<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px; margin-bottom: 30px;">
    <h3 style="color: var(--cor-acento); margin-bottom: 15px;">Upload de XMLs ou pacotes ZIP</h3>
    <form action="{{ route('cte-xmls.store') }}" method="POST" enctype="multipart/form-data">
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
@endpush
@endsection

