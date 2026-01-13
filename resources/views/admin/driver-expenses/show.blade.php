@extends('layouts.app')

@section('page-title', 'Detalhes do Gasto')

@push('styles')
<style>
    .expense-detail-card {
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

    .expense-status-badge {
        display: inline-block;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.9em;
        font-weight: 600;
    }

    .expense-status-badge.pending {
        background: rgba(255, 193, 7, 0.2);
        color: #ffc107;
    }

    .expense-status-badge.approved {
        background: rgba(76, 175, 80, 0.2);
        color: #4caf50;
    }

    .expense-status-badge.rejected {
        background: rgba(244, 67, 54, 0.2);
        color: #f44336;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .detail-item {
        background: rgba(255,255,255,0.05);
        padding: 15px;
        border-radius: 10px;
    }

    .detail-label {
        font-size: 0.85em;
        color: rgba(245, 245, 245, 0.7);
        margin-bottom: 8px;
    }

    .detail-value {
        font-size: 1.1em;
        color: var(--cor-texto-claro);
        font-weight: 600;
    }

    .detail-value.amount {
        font-size: 1.5em;
        color: #f44336;
    }

    .receipt-preview {
        margin-top: 20px;
    }

    .receipt-preview h4 {
        color: var(--cor-acento);
        margin-bottom: 15px;
        font-size: 1.1em;
    }

    .receipt-images {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
        margin-top: 15px;
    }

    .receipt-image-item {
        position: relative;
        aspect-ratio: 1;
        border-radius: 10px;
        overflow: hidden;
        background: var(--cor-principal);
        border: 2px solid rgba(255,255,255,0.1);
        cursor: pointer;
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .receipt-image-item:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(0,0,0,0.5);
        border-color: var(--cor-acento);
    }

    .receipt-image-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .route-link {
        color: var(--cor-acento);
        text-decoration: none;
        font-weight: 600;
        transition: opacity 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .route-link:hover {
        opacity: 0.8;
        text-decoration: underline;
    }

    .route-link i {
        font-size: 0.9em;
    }

    .action-buttons {
        display: flex;
        gap: 15px;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 2px solid rgba(255,255,255,0.1);
    }

    .btn-approve {
        padding: 12px 24px;
        background: #4caf50;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 1em;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-block;
    }

    .btn-approve:hover {
        background: #45a049;
    }

    .btn-reject {
        padding: 12px 24px;
        background: #f44336;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 1em;
        transition: all 0.3s;
    }

    .btn-reject:hover {
        background: #da190b;
    }

    .btn-back {
        padding: 12px 24px;
        background: rgba(255,255,255,0.1);
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 1em;
        text-decoration: none;
        display: inline-block;
        transition: all 0.3s;
    }

    .btn-back:hover {
        background: rgba(255,255,255,0.2);
    }

    .btn-edit {
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

    .btn-edit:hover {
        background: #1976D2;
    }

    .rejection-reason {
        background: rgba(244, 67, 54, 0.1);
        padding: 15px;
        border-radius: 10px;
        border-left: 4px solid #f44336;
        margin-top: 20px;
    }

    .rejection-reason-label {
        font-size: 0.9em;
        color: #f44336;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .rejection-reason-text {
        color: var(--cor-texto-claro);
    }
</style>
@endpush

@section('content')
<div class="expense-detail-card">
    <div class="expense-header">
        <div>
            <h2 class="expense-title">{{ $expense->description }}</h2>
            <span class="expense-status-badge {{ $expense->status }}">{{ $expense->status_label }}</span>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <a href="{{ route('driver-expenses.edit', $expense) }}" class="btn-edit">
                <i class="fas fa-edit"></i> Editar Gasto
            </a>
            <a href="{{ route('driver-expenses.index') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <div class="detail-grid">
        <div class="detail-item">
            <div class="detail-label">Valor</div>
            <div class="detail-value amount">R$ {{ number_format($expense->amount, 2, ',', '.') }}</div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Data do Gasto</div>
            <div class="detail-value">{{ $expense->expense_date->format('d/m/Y') }}</div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Tipo</div>
            <div class="detail-value">{{ $expense->expense_type_label }}</div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Motorista</div>
            <div class="detail-value">{{ $expense->driver->name }}</div>
        </div>

        @if($expense->route)
        <div class="detail-item">
            <div class="detail-label">Rota</div>
            <div class="detail-value">
                <a href="{{ route('routes.show', $expense->route) }}" target="_self" class="route-link" style="color: var(--cor-acento) !important; text-decoration: none !important; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; background: rgba(255, 107, 53, 0.2) !important; border: 2px solid var(--cor-acento); border-radius: 8px; transition: all 0.3s; cursor: pointer !important;" onmouseover="this.style.background='rgba(255, 107, 53, 0.4) !important'; this.style.transform='scale(1.05)'; this.style.textDecoration='none !important';" onmouseout="this.style.background='rgba(255, 107, 53, 0.2) !important'; this.style.transform='scale(1)'; this.style.textDecoration='none !important';" onclick="window.location.href='{{ route('routes.show', $expense->route) }}'; return false;">
                    <i class="fas fa-route"></i> 
                    <span>{{ $expense->route->name }}</span>
                    <i class="fas fa-external-link-alt" style="font-size: 0.85em;"></i>
                    <span style="font-size: 0.85em; opacity: 0.8;">(Clique para ver detalhes)</span>
                </a>
            </div>
        </div>
        @endif

        @if($expense->payment_method)
        <div class="detail-item">
            <div class="detail-label">Forma de Pagamento</div>
            <div class="detail-value">{{ $expense->payment_method }}</div>
        </div>
        @endif

        <div class="detail-item">
            <div class="detail-label">Data de Registro</div>
            <div class="detail-value">{{ $expense->created_at->format('d/m/Y H:i') }}</div>
        </div>
    </div>

    @if($expense->notes)
    <div class="detail-item" style="margin-top: 20px;">
        <div class="detail-label">Observações</div>
        <div class="detail-value" style="font-weight: normal;">{{ $expense->notes }}</div>
    </div>
    @endif

    @if($expense->status === 'rejected' && $expense->rejection_reason)
    <div class="rejection-reason">
        <div class="rejection-reason-label">Motivo da Rejeição</div>
        <div class="rejection-reason-text">{{ $expense->rejection_reason }}</div>
    </div>
    @endif

    @php
        $receiptImages = [];
        $receiptUrlValue = $expense->attributes['receipt_url'] ?? null;
        
        // Try to get images from accessor (which uses proper MinIO URL construction)
        try {
            $receiptImages = $expense->receipt_images ?? [];
        } catch (\Exception $e) {
            // If accessor fails, try direct approach
            \Log::error('Error getting receipt_images', [
                'expense_id' => $expense->id,
                'error' => $e->getMessage(),
            ]);
        }
        
        // Fallback: if receipt_images is empty but receipt_url exists, try to use it directly
        if (empty($receiptImages) && $receiptUrlValue) {
            // Try to generate URL from receipt_url using same MinIO logic as DriverPhoto
            if (filter_var($receiptUrlValue, FILTER_VALIDATE_URL)) {
                // Already a full URL
                $receiptImages = [$receiptUrlValue];
            } else {
                // It's a storage path, try MinIO first (same logic as DriverPhoto)
                try {
                    $minioConfig = config('filesystems.disks.minio');
                    if ($minioConfig && isset($minioConfig['bucket']) && isset($minioConfig['url'])) {
                        $baseUrl = rtrim($minioConfig['url'] ?? '', '/');
                        $bucket = $minioConfig['bucket'] ?? '';
                        $path = ltrim($receiptUrlValue, '/');
                        $minioUrl = "{$baseUrl}/{$bucket}/{$path}";
                        
                        if (filter_var($minioUrl, FILTER_VALIDATE_URL)) {
                            $receiptImages = [$minioUrl];
                        }
                    }
                } catch (\Exception $e) {
                    // Try public disk
                    try {
                        $receiptImages = [\Storage::disk('public')->url($receiptUrlValue)];
                    } catch (\Exception $e2) {
                        // Last resort: try asset
                        $receiptImages = [asset('storage/' . ltrim($receiptUrlValue, '/'))];
                    }
                }
                
                // If still empty, try public disk
                if (empty($receiptImages)) {
                    try {
                        $receiptImages = [\Storage::disk('public')->url($receiptUrlValue)];
                    } catch (\Exception $e) {
                        $receiptImages = [asset('storage/' . ltrim($receiptUrlValue, '/'))];
                    }
                }
            }
        }
    @endphp
    @if(!empty($receiptImages))
    <div class="receipt-preview">
        <h4><i class="fas fa-image"></i> Comprovantes ({{ count($receiptImages) }})</h4>
        <div class="receipt-images">
            @foreach($receiptImages as $index => $imageUrl)
                @if($imageUrl && !empty(trim($imageUrl)))
                <div class="receipt-image-item" style="cursor: pointer;">
                    <img src="{{ $imageUrl }}" alt="Comprovante {{ $index + 1 }}" onclick="openImageModal('{{ $imageUrl }}', {{ $index + 1 }}, {{ count($receiptImages) }})" style="width: 100%; height: 100%; object-fit: cover; cursor: pointer;" onerror="console.error('Erro ao carregar imagem:', '{{ $imageUrl }}'); this.style.display='none'; this.parentElement.innerHTML='<div style=\\'padding: 20px; text-align: center; color: rgba(245,245,245,0.5);\\'><i class=\\'fas fa-image\\'></i><br>Imagem não encontrada</div>';">
                </div>
                @endif
            @endforeach
        </div>
    </div>
    @endif

    @if($expense->status === 'pending')
    <div class="action-buttons">
        <button onclick="approveExpense({{ $expense->id }})" class="btn-approve">
            <i class="fas fa-check"></i> Aprovar Gasto
        </button>
        <button onclick="rejectExpense({{ $expense->id }})" class="btn-reject">
            <i class="fas fa-times"></i> Rejeitar Gasto
        </button>
    </div>
    @endif
</div>

<!-- Reject Modal -->
<div id="rejectModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 2000; align-items: center; justify-content: center;">
    <div style="background: var(--cor-secundaria); padding: 30px; border-radius: 15px; max-width: 500px; width: 90%;">
        <h3 style="color: var(--cor-acento); margin-bottom: 20px;">Rejeitar Gasto</h3>
        <form id="rejectForm" onsubmit="submitReject(event)">
            <input type="hidden" id="rejectExpenseId" name="expense_id">
            <div style="margin-bottom: 20px;">
                <label style="display: block; color: var(--cor-texto-claro); margin-bottom: 8px;">Motivo da Rejeição *</label>
                <textarea id="rejectionReason" name="rejection_reason" required rows="4" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);" placeholder="Informe o motivo da rejeição..."></textarea>
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" style="flex: 1; padding: 12px; background: #f44336; color: white; border: none; border-radius: 8px; cursor: pointer;">
                    Rejeitar
                </button>
                <button type="button" onclick="closeRejectModal()" style="flex: 1; padding: 12px; background: rgba(255,255,255,0.1); color: white; border: none; border-radius: 8px; cursor: pointer;">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
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

    function approveExpense(expenseId) {
        if (!confirm('Deseja realmente aprovar este gasto?')) {
            return;
        }

        fetch(`/driver-expenses/${expenseId}/approve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Gasto aprovado com sucesso!');
                window.location.reload();
            } else {
                alert('Erro: ' + (data.error || 'Erro desconhecido'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erro ao aprovar gasto. Tente novamente.');
        });
    }

    function rejectExpense(expenseId) {
        document.getElementById('rejectExpenseId').value = expenseId;
        document.getElementById('rejectionReason').value = '';
        document.getElementById('rejectModal').style.display = 'flex';
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').style.display = 'none';
    }

    function submitReject(event) {
        event.preventDefault();
        
        const expenseId = document.getElementById('rejectExpenseId').value;
        const reason = document.getElementById('rejectionReason').value;

        if (!reason.trim()) {
            alert('Por favor, informe o motivo da rejeição.');
            return;
        }

        fetch(`/driver-expenses/${expenseId}/reject`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                rejection_reason: reason
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Gasto rejeitado.');
                window.location.reload();
            } else {
                alert('Erro: ' + (data.error || 'Erro desconhecido'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erro ao rejeitar gasto. Tente novamente.');
        });
    }

    // Functions are now defined in the scripts section above
</script>
@endpush






