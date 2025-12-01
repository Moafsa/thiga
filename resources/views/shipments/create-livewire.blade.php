@extends('layouts.app')

@section('title', 'Criar Carga - TMS SaaS')
@section('page-title', 'Nova Carga')

@push('styles')
@include('shared.styles')
@endpush

@section('content')
<div style="max-width: 1200px; margin: 0 auto;">
    @livewire('create-shipment')
</div>
@endsection


