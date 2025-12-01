@extends('layouts.app')

@section('title', 'Driver Details - TMS SaaS')
@section('page-title', 'Driver Details')

@push('styles')
@include('shared.styles')
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">{{ $driver->name }}</h1>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="{{ route('drivers.edit', $driver) }}" class="btn-primary">Edit</a>
        <a href="{{ route('drivers.index') }}" class="btn-secondary">Back</a>
    </div>
</div>

<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px;">
    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">Driver Information</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Name:</span>
            <span style="color: var(--cor-texto-claro); font-weight: 600;">{{ $driver->name }}</span>
        </div>
        @if($driver->email)
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Email:</span>
            <span style="color: var(--cor-texto-claro); font-weight: 600;">{{ $driver->email }}</span>
        </div>
        @endif
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Status:</span>
            <span class="status-badge" style="background-color: {{ $driver->is_active ? 'rgba(76, 175, 80, 0.2)' : 'rgba(244, 67, 54, 0.2)' }}; color: {{ $driver->is_active ? '#4caf50' : '#f44336' }};">
                {{ $driver->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>
    </div>
</div>

@if($driver->vehicles->count() > 0)
<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px; margin-top: 20px;">
    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">Assigned Vehicles</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 15px;">
        @foreach($driver->vehicles as $vehicle)
            <div style="background-color: var(--cor-principal); padding: 20px; border-radius: 10px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <a href="{{ route('vehicles.show', $vehicle) }}" style="color: var(--cor-acento); font-weight: 600; text-decoration: none; font-size: 1.1em;">
                            {{ $vehicle->formatted_plate }}
                        </a>
                        @if($vehicle->brand && $vehicle->model)
                            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin-top: 5px;">
                                {{ $vehicle->brand }} {{ $vehicle->model }}
                            </p>
                        @endif
                        <span class="status-badge" style="background-color: rgba(33, 150, 243, 0.2); color: #2196F3; margin-top: 10px; display: inline-block;">
                            {{ $vehicle->status_label }}
                        </span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif
@endsection







