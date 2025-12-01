@forelse($mdfes as $mdfe)
    <tr>
        <td>
            <div style="font-weight: 600;">{{ $mdfe->mitt_number ?? 'N/A' }}</div>
        </td>
        <td>
            @if($mdfe->access_key)
                <div class="access-key">{{ substr($mdfe->access_key, 0, 20) }}...</div>
            @else
                <span style="opacity: 0.5;">-</span>
            @endif
        </td>
        <td>
            @if($mdfe->route)
                <div>{{ $mdfe->route->name }}</div>
                @if($mdfe->route->scheduled_date)
                    <div style="opacity: 0.7; font-size: 0.9em;">{{ $mdfe->route->scheduled_date->format('d/m/Y') }}</div>
                @endif
            @else
                <span style="opacity: 0.5;">N/A</span>
            @endif
        </td>
        <td>
            @if($mdfe->route && $mdfe->route->driver)
                <div>{{ $mdfe->route->driver->name }}</div>
            @else
                <span style="opacity: 0.5;">N/A</span>
            @endif
        </td>
        <td>
            <div>{{ $mdfe->created_at->format('d/m/Y') }}</div>
            <div style="opacity: 0.7; font-size: 0.9em;">{{ $mdfe->created_at->format('H:i') }}</div>
            @if($mdfe->authorized_at)
                <div style="opacity: 0.6; font-size: 0.85em; margin-top: 3px;">
                    Autorizado: {{ $mdfe->authorized_at->format('d/m/Y H:i') }}
                </div>
            @endif
        </td>
        <td>
            <span class="status-badge status-{{ $mdfe->status }}">
                {{ $mdfe->status_label }}
            </span>
        </td>
        <td>
            @if($mdfe->route)
                @php
                    $cteCount = \App\Models\FiscalDocument::where('tenant_id', $mdfe->tenant_id)
                        ->where('document_type', 'cte')
                        ->whereHas('shipment', function($q) use ($mdfe) {
                            $q->where('route_id', $mdfe->route_id);
                        })
                        ->count();
                @endphp
                <span style="font-weight: 600;">{{ $cteCount }}</span>
            @else
                <span style="opacity: 0.5;">-</span>
            @endif
        </td>
        <td>
            <div class="action-buttons">
                <a href="{{ route('fiscal.mdfes.show', $mdfe) }}" class="action-btn" title="Ver detalhes">
                    <i class="fas fa-eye"></i>
                </a>
                @if($mdfe->pdf_url)
                    <a href="{{ $mdfe->pdf_url }}" target="_blank" class="action-btn" title="Ver PDF">
                        <i class="fas fa-file-pdf"></i>
                    </a>
                @endif
                @if($mdfe->xml_url)
                    <a href="{{ $mdfe->xml_url }}" target="_blank" class="action-btn" title="Ver XML">
                        <i class="fas fa-code"></i>
                    </a>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="empty-state">
            <i class="fas fa-file-invoice"></i>
            <h3>Nenhum MDF-e encontrado</h3>
            <p>Nenhum MDF-e foi emitido ainda ou não corresponde aos filtros aplicados</p>
        </td>
    </tr>
@endforelse

