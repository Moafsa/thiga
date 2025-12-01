@php
    $fiscalDocument = $fiscalDocument ?? null;
    $documentType = $documentType ?? 'cte';
@endphp

@if($fiscalDocument)
<div class="fiscal-timeline" style="background-color: var(--cor-secundaria); padding: 25px; border-radius: 15px; margin-top: 20px;">
    <h3 style="color: var(--cor-acento); margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-file-invoice"></i>
        Fiscal Document Status - {{ strtoupper($fiscalDocument->document_type) }}
    </h3>

    <div class="timeline-steps">
        @php
            $steps = [
                'pending' => ['icon' => 'clock', 'label' => 'Pending', 'color' => '#ffc107'],
                'validating' => ['icon' => 'check-circle', 'label' => 'Validating', 'color' => '#17a2b8'],
                'processing' => ['icon' => 'spinner', 'label' => 'Processing', 'color' => '#17a2b8'],
                'authorized' => ['icon' => 'check-circle', 'label' => 'Authorized', 'color' => '#28a745'],
                'rejected' => ['icon' => 'times-circle', 'label' => 'Rejected', 'color' => '#dc3545'],
                'error' => ['icon' => 'exclamation-triangle', 'label' => 'Error', 'color' => '#dc3545'],
                'cancelled' => ['icon' => 'ban', 'label' => 'Cancelled', 'color' => '#6c757d'],
            ];
            
            $currentStep = $fiscalDocument->status;
            $stepOrder = ['pending', 'validating', 'processing', 'authorized'];
            $currentIndex = array_search($currentStep, $stepOrder);
        @endphp

        @foreach($stepOrder as $index => $step)
            @php
                $stepData = $steps[$step] ?? $steps['pending'];
                $isActive = $index <= $currentIndex;
                $isCurrent = $step === $currentStep;
            @endphp
            <div class="timeline-step {{ $isActive ? 'active' : '' }} {{ $isCurrent ? 'current' : '' }}" 
                 style="display: flex; align-items: center; margin-bottom: 20px; position: relative;">
                <div class="step-icon" 
                     style="width: 50px; height: 50px; border-radius: 50%; background-color: {{ $isActive ? $stepData['color'] : 'rgba(255,255,255,0.1)' }}; 
                            display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; 
                            margin-right: 15px; flex-shrink: 0;">
                    @if($isCurrent && $step === 'processing')
                        <i class="fas fa-{{ $stepData['icon'] }} fa-spin"></i>
                    @else
                        <i class="fas fa-{{ $stepData['icon'] }}"></i>
                    @endif
                </div>
                <div class="step-content" style="flex: 1;">
                    <div style="color: var(--cor-texto-claro); font-weight: 600; margin-bottom: 5px;">
                        {{ $stepData['label'] }}
                    </div>
                    @if($isCurrent)
                        <div style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">
                            @if($fiscalDocument->error_message)
                                <span style="color: #dc3545;">{{ $fiscalDocument->error_message }}</span>
                            @else
                                Processing with Mitt...
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    @if($fiscalDocument->isAuthorized())
        <div class="fiscal-document-links" style="margin-top: 25px; padding-top: 25px; border-top: 2px solid rgba(255, 107, 53, 0.3);">
            <h4 style="color: var(--cor-texto-claro); margin-bottom: 15px;">Document Information</h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                @if($fiscalDocument->access_key)
                    <div>
                        <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Access Key:</span>
                        <div style="color: var(--cor-texto-claro); font-weight: 600; word-break: break-all;">
                            {{ $fiscalDocument->access_key }}
                        </div>
                    </div>
                @endif
                @if($fiscalDocument->mitt_number)
                    <div>
                        <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Number:</span>
                        <div style="color: var(--cor-texto-claro); font-weight: 600;">
                            {{ $fiscalDocument->mitt_number }}
                        </div>
                    </div>
                @endif
                @if($fiscalDocument->authorized_at)
                    <div>
                        <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Authorized At:</span>
                        <div style="color: var(--cor-texto-claro); font-weight: 600;">
                            {{ $fiscalDocument->authorized_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                @endif
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                @if($fiscalDocument->pdf_url)
                    <a href="{{ $fiscalDocument->pdf_url }}" target="_blank" class="btn-primary" style="padding: 10px 20px;">
                        <i class="fas fa-file-pdf"></i>
                        Download PDF
                    </a>
                @endif
                @if($fiscalDocument->xml_url)
                    <a href="{{ $fiscalDocument->xml_url }}" target="_blank" class="btn-secondary" style="padding: 10px 20px;">
                        <i class="fas fa-file-code"></i>
                        Download XML
                    </a>
                @endif
            </div>
        </div>
    @endif

    @if($fiscalDocument->hasError() && $fiscalDocument->error_message)
        <div class="fiscal-error" style="margin-top: 20px; padding: 15px; background-color: rgba(220, 53, 69, 0.2); border-radius: 10px; border-left: 4px solid #dc3545;">
            <h4 style="color: #dc3545; margin-bottom: 10px;">
                <i class="fas fa-exclamation-triangle"></i>
                Error Details
            </h4>
            <p style="color: var(--cor-texto-claro); margin-bottom: 5px;">{{ $fiscalDocument->error_message }}</p>
            @if($fiscalDocument->error_details)
                <details style="margin-top: 10px;">
                    <summary style="color: rgba(245, 245, 245, 0.7); cursor: pointer;">View technical details</summary>
                    <pre style="color: rgba(245, 245, 245, 0.9); margin-top: 10px; padding: 10px; background-color: rgba(0,0,0,0.3); border-radius: 5px; overflow-x: auto;">{{ json_encode($fiscalDocument->error_details, JSON_PRETTY_PRINT) }}</pre>
                </details>
            @endif
        </div>
    @endif
</div>
@endif

















