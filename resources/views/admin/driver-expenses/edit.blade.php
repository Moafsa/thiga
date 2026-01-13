@extends('layouts.app')

@section('page-title', 'Editar Gasto')

@push('styles')
<style>
    .expense-edit-card {
        background: var(--cor-secundaria);
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }

    .expense-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid rgba(255,255,255,0.1);
    }

    .expense-title {
        font-size: 1.5em;
        color: var(--cor-acento);
        margin-bottom: 10px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        color: rgba(245, 245, 245, 0.7);
        font-size: 0.9em;
        margin-bottom: 8px;
        font-weight: 600;
    }

    .form-input, .form-select, .form-textarea {
        width: 100%;
        padding: 12px;
        border-radius: 8px;
        border: 1px solid rgba(255,255,255,0.1);
        background: var(--cor-principal);
        color: var(--cor-texto-claro);
        font-size: 1em;
        transition: all 0.3s;
    }

    .form-input:focus, .form-select:focus, .form-textarea:focus {
        outline: none;
        border-color: var(--cor-acento);
        box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .receipt-upload {
        margin-top: 15px;
        padding: 20px;
        background: rgba(255,255,255,0.05);
        border-radius: 10px;
        border: 2px dashed rgba(255,255,255,0.2);
    }

    .receipt-preview {
        margin-top: 15px;
    }

    .receipt-preview img {
        max-width: 200px;
        max-height: 200px;
        border-radius: 8px;
        cursor: pointer;
        border: 2px solid rgba(255,255,255,0.1);
        transition: transform 0.3s;
    }

    .receipt-preview img:hover {
        transform: scale(1.1);
        border-color: var(--cor-acento);
    }

    .btn-submit {
        padding: 12px 24px;
        background: #2196F3;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 1em;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-block;
    }

    .btn-submit:hover {
        background: #1976D2;
    }

    .btn-cancel {
        padding: 12px 24px;
        background: rgba(255,255,255,0.1);
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 1em;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-block;
    }

    .btn-cancel:hover {
        background: rgba(255,255,255,0.2);
    }
</style>
@endpush

@section('content')
<div class="expense-edit-card">
    <div class="expense-header">
        <div>
            <h2 class="expense-title">Editar Gasto</h2>
            <span class="expense-status-badge {{ $expense->status }}" style="display: inline-block; padding: 8px 16px; border-radius: 20px; font-size: 0.9em; font-weight: 600; background: {{ $expense->status === 'approved' ? 'rgba(76, 175, 80, 0.2)' : ($expense->status === 'rejected' ? 'rgba(244, 67, 54, 0.2)' : 'rgba(255, 193, 7, 0.2)') }}; color: {{ $expense->status === 'approved' ? '#4caf50' : ($expense->status === 'rejected' ? '#f44336' : '#ffc107') }};">
                {{ $expense->status_label }}
            </span>
        </div>
        <a href="{{ route('driver-expenses.show', $expense) }}" class="btn-cancel">
            <i class="fas fa-arrow-left"></i> Cancelar
        </a>
    </div>

    <form method="POST" action="{{ route('driver-expenses.update', $expense) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Tipo de Gasto *</label>
                <select name="expense_type" class="form-select" required>
                    <option value="toll" {{ old('expense_type', $expense->expense_type) === 'toll' ? 'selected' : '' }}>Pedágio</option>
                    <option value="fuel" {{ old('expense_type', $expense->expense_type) === 'fuel' ? 'selected' : '' }}>Combustível</option>
                    <option value="meal" {{ old('expense_type', $expense->expense_type) === 'meal' ? 'selected' : '' }}>Refeição</option>
                    <option value="parking" {{ old('expense_type', $expense->expense_type) === 'parking' ? 'selected' : '' }}>Estacionamento</option>
                    <option value="other" {{ old('expense_type', $expense->expense_type) === 'other' ? 'selected' : '' }}>Outro</option>
                </select>
                @error('expense_type')
                    <p style="color: #f44336; font-size: 0.85em; margin-top: 5px;">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Valor (R$) *</label>
                <input type="number" name="amount" step="0.01" min="0.01" value="{{ old('amount', $expense->amount) }}" class="form-input" required>
                @error('amount')
                    <p style="color: #f44336; font-size: 0.85em; margin-top: 5px;">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Data do Gasto *</label>
                <input type="date" name="expense_date" value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}" class="form-input" required>
                @error('expense_date')
                    <p style="color: #f44336; font-size: 0.85em; margin-top: 5px;">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Forma de Pagamento</label>
                <select name="payment_method" class="form-select">
                    <option value="">Selecione...</option>
                    <option value="Dinheiro" {{ old('payment_method', $expense->payment_method) === 'Dinheiro' ? 'selected' : '' }}>Dinheiro</option>
                    <option value="Cartão de Débito" {{ old('payment_method', $expense->payment_method) === 'Cartão de Débito' ? 'selected' : '' }}>Cartão de Débito</option>
                    <option value="Cartão de Crédito" {{ old('payment_method', $expense->payment_method) === 'Cartão de Crédito' ? 'selected' : '' }}>Cartão de Crédito</option>
                    <option value="PIX" {{ old('payment_method', $expense->payment_method) === 'PIX' ? 'selected' : '' }}>PIX</option>
                    <option value="Outro" {{ old('payment_method', $expense->payment_method) === 'Outro' ? 'selected' : '' }}>Outro</option>
                </select>
                @error('payment_method')
                    <p style="color: #f44336; font-size: 0.85em; margin-top: 5px;">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label">Rota</label>
                <select name="route_id" class="form-select">
                    <option value="">Selecione uma rota...</option>
                    @foreach($routes as $route)
                        <option value="{{ $route->id }}" {{ old('route_id', $expense->route_id) == $route->id ? 'selected' : '' }}>
                            {{ $route->name }} - {{ $route->scheduled_date->format('d/m/Y') }}
                        </option>
                    @endforeach
                </select>
                @error('route_id')
                    <p style="color: #f44336; font-size: 0.85em; margin-top: 5px;">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label">Descrição *</label>
                <input type="text" name="description" value="{{ old('description', $expense->description) }}" class="form-input" required>
                @error('description')
                    <p style="color: #f44336; font-size: 0.85em; margin-top: 5px;">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label">Observações</label>
                <textarea name="notes" rows="4" class="form-textarea">{{ old('notes', $expense->notes) }}</textarea>
                @error('notes')
                    <p style="color: #f44336; font-size: 0.85em; margin-top: 5px;">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label">Comprovante</label>
                <div class="receipt-upload">
                    <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin-bottom: 10px;">
                        Se você enviar uma nova imagem, ela substituirá o comprovante atual.
                    </p>
                    <input type="file" name="receipt" accept="image/*" class="form-input" style="padding: 8px;">
                    @error('receipt')
                        <p style="color: #f44336; font-size: 0.85em; margin-top: 5px;">{{ $message }}</p>
                    @enderror
                    
                    @php
                        $receiptImages = $expense->receipt_images ?? [];
                        if (empty($receiptImages) && $expense->receipt_url) {
                            $receiptImages = [$expense->receipt_url];
                        }
                    @endphp
                    @if(!empty($receiptImages))
                    <div class="receipt-preview">
                        <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin-bottom: 10px;">Comprovante Atual:</p>
                        @foreach($receiptImages as $index => $imageUrl)
                            @if($imageUrl)
                            <img src="{{ $imageUrl }}" alt="Comprovante atual" onclick="openImageModal('{{ $imageUrl }}', {{ $index + 1 }}, {{ count($receiptImages) }})" style="margin-right: 10px; margin-bottom: 10px;">
                            @endif
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 15px; margin-top: 30px; padding-top: 20px; border-top: 2px solid rgba(255,255,255,0.1);">
            <button type="submit" class="btn-submit">
                <i class="fas fa-save"></i> Salvar Alterações
            </button>
            <a href="{{ route('driver-expenses.show', $expense) }}" class="btn-cancel">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function openImageModal(imageUrl, currentIndex, totalImages) {
        openImageModalAll([imageUrl], currentIndex - 1);
    }

    function openImageModalAll(imageUrls, startIndex) {
        if (!imageUrls || imageUrls.length === 0) return;
        
        let currentIndex = Math.max(0, Math.min(startIndex || 0, imageUrls.length - 1));
        
        const modal = document.createElement('div');
        modal.className = 'image-modal';
        modal.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.95); z-index: 10000; display: flex; align-items: center; justify-content: center;';
        
        function updateImage() {
            if (currentIndex < 0 || currentIndex >= imageUrls.length) return;
            
            const imageContainer = modal.querySelector('.image-container');
            if (imageContainer) {
                imageContainer.innerHTML = `
                    <img src="${imageUrls[currentIndex]}" alt="Comprovante ${currentIndex + 1}" style="max-width: 100%; max-height: 90vh; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.5);">
                    <div style="color: white; text-align: center; margin-top: 15px;">
                        <p style="margin: 0; font-weight: 600;">Comprovante ${currentIndex + 1} de ${imageUrls.length}</p>
                    </div>
                `;
                
                // Update navigation buttons
                const prevBtn = modal.querySelector('.nav-btn-prev');
                const nextBtn = modal.querySelector('.nav-btn-next');
                if (prevBtn) prevBtn.style.display = imageUrls.length > 1 && currentIndex > 0 ? 'flex' : 'none';
                if (nextBtn) nextBtn.style.display = imageUrls.length > 1 && currentIndex < imageUrls.length - 1 ? 'flex' : 'none';
            }
        }
        
        modal.innerHTML = `
            <div style="position: relative; max-width: 90%; max-height: 90%; text-align: center; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
                <button onclick="this.closest('.image-modal').remove(); document.removeEventListener('keydown', handleKeyPress);" style="position: absolute; top: 20px; right: 20px; background: rgba(255,255,255,0.2); color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; font-size: 1.5em; z-index: 10001;">&times;</button>
                ${imageUrls.length > 1 ? `
                    <button class="nav-btn-prev" style="position: absolute; left: 20px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.2); color: white; border: none; padding: 15px; border-radius: 50%; cursor: pointer; font-size: 1.5em; z-index: 10001; display: ${currentIndex > 0 ? 'flex' : 'none'}; align-items: center; justify-content: center; width: 50px; height: 50px;">&lt;</button>
                    <button class="nav-btn-next" style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.2); color: white; border: none; padding: 15px; border-radius: 50%; cursor: pointer; font-size: 1.5em; z-index: 10001; display: ${currentIndex < imageUrls.length - 1 ? 'flex' : 'none'}; align-items: center; justify-content: center; width: 50px; height: 50px;">&gt;</button>
                ` : ''}
                <div class="image-container"></div>
            </div>
        `;
        
        document.body.appendChild(modal);
        updateImage();
        
        // Navigation buttons
        const prevBtn = modal.querySelector('.nav-btn-prev');
        const nextBtn = modal.querySelector('.nav-btn-next');
        
        if (prevBtn) {
            prevBtn.onclick = function(e) {
                e.stopPropagation();
                if (currentIndex > 0) {
                    currentIndex--;
                    updateImage();
                }
            };
        }
        
        if (nextBtn) {
            nextBtn.onclick = function(e) {
                e.stopPropagation();
                if (currentIndex < imageUrls.length - 1) {
                    currentIndex++;
                    updateImage();
                }
            };
        }
        
        // Keyboard navigation
        const handleKeyPress = (e) => {
            if (e.key === 'ArrowLeft' && currentIndex > 0) {
                e.preventDefault();
                currentIndex--;
                updateImage();
            } else if (e.key === 'ArrowRight' && currentIndex < imageUrls.length - 1) {
                e.preventDefault();
                currentIndex++;
                updateImage();
            } else if (e.key === 'Escape') {
                modal.remove();
                document.removeEventListener('keydown', handleKeyPress);
            }
        };
        
        document.addEventListener('keydown', handleKeyPress);
        
        modal.onclick = function(e) {
            if (e.target === modal || (e.target.classList && e.target.classList.contains('image-modal'))) {
                modal.remove();
                document.removeEventListener('keydown', handleKeyPress);
            }
        };
        
        // Prevent image click from closing modal
        const imgContainer = modal.querySelector('.image-container');
        if (imgContainer) {
            imgContainer.onclick = function(e) {
                e.stopPropagation();
            };
        }
    }
</script>
@endpush
