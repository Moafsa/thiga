@if ($paginator->hasPages())
<nav class="app-pagination" aria-label="Navegação de páginas">
    <style>
        .app-pagination { font-size: 1rem; line-height: 1.5; }
        .app-pagination ul { display: flex; flex-wrap: wrap; gap: 8px; list-style: none; margin: 0; padding: 0; align-items: center; }
        .app-pagination li { margin: 0; }
        .app-pagination a,
        .app-pagination span { display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 10px; border-radius: 8px; text-decoration: none; font-size: 0.95rem; font-weight: 500; transition: background 0.2s, color 0.2s; }
        .app-pagination a { background: rgba(255,255,255,0.1); color: var(--cor-texto-claro, #F5F5F5); border: 1px solid rgba(255,255,255,0.2); }
        .app-pagination a:hover { background: rgba(255, 107, 53, 0.3); color: var(--cor-acento, #FF6B35); border-color: rgba(255, 107, 53, 0.5); }
        .app-pagination .active span { background: var(--cor-acento, #FF6B35); color: #1a3d33; border: 1px solid transparent; }
        .app-pagination .disabled span { background: transparent; color: rgba(245,245,245,0.4); border: 1px solid rgba(255,255,255,0.1); cursor: default; }
    </style>
    <ul>
        @if ($paginator->onFirstPage())
            <li class="disabled" aria-disabled="true"><span>Anterior</span></li>
        @else
            <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev">Anterior</a></li>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <li class="disabled"><span>{{ $element }}</span></li>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="active" aria-current="page"><span>{{ $page }}</span></li>
                    @else
                        <li><a href="{{ $url }}">{{ $page }}</a></li>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <li><a href="{{ $paginator->nextPageUrl() }}" rel="next">Próxima</a></li>
        @else
            <li class="disabled" aria-disabled="true"><span>Próxima</span></li>
        @endif
    </ul>
</nav>
@endif
