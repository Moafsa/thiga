@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Fiscal Documents</h1>
        <p class="text-gray-600 mt-2">View and manage all CT-e and MDF-e documents</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        {{-- Filters Sidebar --}}
        <div class="lg:col-span-1">
            @include('fiscal.partials.filter-form')
        </div>

        {{-- Main Content --}}
        <div class="lg:col-span-3">
            {{-- Results Summary --}}
            <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <p class="text-sm text-blue-900">
                    <strong>{{ $documents->total() }}</strong> document{{ $documents->total() !== 1 ? 's' : '' }} found
                    @if (request('document_type') || request('status') || request('search'))
                        <span class="text-gray-500">(filtered)</span>
                    @endif
                </p>
            </div>

            {{-- Documents Table --}}
            @if ($documents->count() > 0)
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-100 border-b">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Type</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Number</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Access Key</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Related Entity</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Created</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse ($documents as $doc)
                                <tr class="hover:bg-gray-50 transition">
                                    {{-- Type --}}
                                    <td class="px-4 py-3">
                                        @if ($doc->isCte())
                                            <span class="inline-flex items-center gap-1 text-blue-600 font-medium">
                                                📋 CT-e
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-green-600 font-medium">
                                                📦 MDF-e
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Number --}}
                                    <td class="px-4 py-3 font-semibold text-gray-900">
                                        {{ $doc->mitt_number ?? 'N/A' }}
                                    </td>

                                    {{-- Access Key --}}
                                    <td class="px-4 py-3">
                                        <code class="text-xs bg-gray-100 px-2 py-1 rounded font-mono text-gray-700">
                                            {{ substr($doc->access_key ?? '', 0, 8) }}...
                                        </code>
                                        <div class="text-xs text-gray-500 mt-1">
                                            ID: {{ $doc->mitt_id ? substr($doc->mitt_id, -6) : 'N/A' }}
                                        </div>
                                    </td>

                                    {{-- Related Entity --}}
                                    <td class="px-4 py-3">
                                        @if ($doc->shipment)
                                            <a href="{{ route('shipments.show', $doc->shipment) }}"
                                               class="text-blue-600 hover:text-blue-800 hover:underline">
                                                Shipment #{{ $doc->shipment->id }}
                                            </a>
                                            <div class="text-xs text-gray-500 mt-1">
                                                {{ $doc->shipment->senderClient?->name ?? 'Unknown' }}
                                            </div>
                                        @elseif ($doc->route)
                                            <a href="{{ route('routes.show', $doc->route) }}"
                                               class="text-blue-600 hover:text-blue-800 hover:underline">
                                                Route #{{ $doc->route->id }}
                                            </a>
                                            <div class="text-xs text-gray-500 mt-1">
                                                Driver: {{ $doc->route->driver?->name ?? 'Unassigned' }}
                                            </div>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>

                                    {{-- Status --}}
                                    <td class="px-4 py-3">
                                        @php
                                            $statusColors = [
                                                'pending' => 'bg-yellow-100 text-yellow-800',
                                                'validating' => 'bg-blue-100 text-blue-800',
                                                'processing' => 'bg-purple-100 text-purple-800',
                                                'authorized' => 'bg-green-100 text-green-800',
                                                'rejected' => 'bg-red-100 text-red-800',
                                                'cancelled' => 'bg-gray-100 text-gray-800',
                                                'error' => 'bg-red-100 text-red-800',
                                            ];
                                            $statusEmojis = [
                                                'pending' => '🟡',
                                                'validating' => '🔵',
                                                'processing' => '🟣',
                                                'authorized' => '🟢',
                                                'rejected' => '🔴',
                                                'cancelled' => '⚪',
                                                'error' => '🔴',
                                            ];
                                        @endphp
                                        <span class="inline-block px-2 py-1 rounded text-xs font-semibold {{ $statusColors[$doc->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $statusEmojis[$doc->status] ?? '' }}
                                            {{ ucfirst(str_replace('_', ' ', $doc->status)) }}
                                        </span>
                                    </td>

                                    {{-- Created Date --}}
                                    <td class="px-4 py-3 text-gray-600">
                                        {{ $doc->created_at->format('d/m/Y H:i') }}
                                        @if ($doc->authorized_at)
                                            <div class="text-xs text-green-600 mt-1">
                                                Auth: {{ $doc->authorized_at->format('d/m/Y') }}
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Actions --}}
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center gap-1">
                                            <a href="{{ route('fiscal.documents.show', $doc) }}"
                                               class="p-2 hover:bg-blue-100 rounded transition" title="View">
                                                👁️
                                            </a>

                                            @if ($doc->pdf_url)
                                                <a href="{{ $doc->pdf_url }}" target="_blank" rel="noopener"
                                                   class="p-2 hover:bg-green-100 rounded transition" title="Download PDF">
                                                    📄
                                                </a>
                                            @endif

                                            @if ($doc->xml_url)
                                                <a href="{{ $doc->xml_url }}" target="_blank" rel="noopener"
                                                   class="p-2 hover:bg-yellow-100 rounded transition" title="Download XML">
                                                    📝
                                                </a>
                                            @endif

                                            @if ($doc->isAuthorized() && $doc->isCte())
                                                <form method="POST"
                                                      action="{{ route('fiscal.cancel-cte', $doc) }}"
                                                      class="inline"
                                                      onsubmit="return confirm('Are you sure you want to cancel this CT-e? This action cannot be undone.');">
                                                    @csrf
                                                    <button type="submit" class="p-2 hover:bg-red-100 rounded transition" title="Cancel CT-e">
                                                        ❌
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center">
                                        <div class="text-gray-500">
                                            <p class="text-lg mb-2">📭 No documents found</p>
                                            <p class="text-sm">Try adjusting your filters or check back later.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-6">
                    {{ $documents->links() }}
                </div>
            @else
                {{-- Empty State --}}
                <div class="bg-white rounded-lg shadow-md p-12 text-center">
                    <p class="text-2xl mb-2">📭</p>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No Fiscal Documents</h3>
                    <p class="text-gray-600 mb-4">You don't have any fiscal documents yet.</p>
                    <a href="{{ route('shipments.create') }}" class="inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Create a Shipment
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .hover\:bg-blue-100:hover {
        background-color: #dbeafe;
    }
    .hover\:bg-green-100:hover {
        background-color: #dcfce7;
    }
    .hover\:bg-yellow-100:hover {
        background-color: #fef3c7;
    }
    .hover\:bg-red-100:hover {
        background-color: #fee2e2;
    }
</style>
@endsection
