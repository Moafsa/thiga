<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrchestrateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer' => ['required', 'array'],
            'customer.name' => ['required', 'string', 'max:255'],
            'customer.document' => ['nullable', 'string', 'max:32'],
            'customer.email' => ['nullable', 'email', 'max:255'],
            'customer.phone' => ['nullable', 'string', 'max:30'],
            'customer.salesperson_id' => ['nullable', 'integer', Rule::exists('salespeople', 'id')],
            'customer.allow_update' => ['sometimes', 'boolean'],
            'customer.addresses' => ['nullable', 'array'],
            'customer.addresses.*.type' => ['required_with:customer.addresses', Rule::in(['pickup', 'delivery', 'billing', 'other'])],
            'customer.addresses.*.name' => ['nullable', 'string', 'max:255'],
            'customer.addresses.*.street' => ['required_with:customer.addresses', 'string', 'max:255'],
            'customer.addresses.*.number' => ['nullable', 'string', 'max:25'],
            'customer.addresses.*.complement' => ['nullable', 'string', 'max:255'],
            'customer.addresses.*.neighborhood' => ['nullable', 'string', 'max:255'],
            'customer.addresses.*.city' => ['required_with:customer.addresses', 'string', 'max:255'],
            'customer.addresses.*.state' => ['required_with:customer.addresses', 'string', 'size:2'],
            'customer.addresses.*.zip_code' => ['nullable', 'string', 'max:10'],
            'customer.addresses.*.is_default' => ['sometimes', 'boolean'],

            'freight' => ['required', 'array'],
            'freight.destination' => ['required', 'string', 'max:255'],
            'freight.weight' => ['nullable', 'numeric', 'min:0'],
            'freight.cubage' => ['nullable', 'numeric', 'min:0'],
            'freight.invoice_value' => ['nullable', 'numeric', 'min:0'],
            'freight.options' => ['nullable', 'array'],

            'shipment' => ['required', 'array'],
            'shipment.title' => ['nullable', 'string', 'max:255'],
            'shipment.notes' => ['nullable', 'string', 'max:5000'],
            'shipment.pickup' => ['required', 'array'],
            'shipment.pickup.address' => ['required', 'string', 'max:255'],
            'shipment.pickup.city' => ['required', 'string', 'max:255'],
            'shipment.pickup.state' => ['required', 'string', 'size:2'],
            'shipment.pickup.zip_code' => ['nullable', 'string', 'max:10'],
            'shipment.pickup.latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'shipment.pickup.longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'shipment.pickup.date' => ['required', 'date'],
            'shipment.pickup.time' => ['nullable', 'date_format:H:i'],

            'shipment.delivery' => ['required', 'array'],
            'shipment.delivery.address' => ['required', 'string', 'max:255'],
            'shipment.delivery.city' => ['required', 'string', 'max:255'],
            'shipment.delivery.state' => ['required', 'string', 'size:2'],
            'shipment.delivery.zip_code' => ['nullable', 'string', 'max:10'],
            'shipment.delivery.latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'shipment.delivery.longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'shipment.delivery.date' => ['nullable', 'date'],
            'shipment.delivery.time' => ['nullable', 'date_format:H:i'],
            'shipment.delivery.contact_name' => ['nullable', 'string', 'max:255'],
            'shipment.delivery.contact_phone' => ['nullable', 'string', 'max:30'],
            'shipment.delivery.contact_email' => ['nullable', 'email', 'max:255'],

            'shipment.items' => ['nullable', 'array'],
            'shipment.items.*.description' => ['nullable', 'string', 'max:255'],
            'shipment.items.*.quantity' => ['nullable', 'integer', 'min:1'],
            'shipment.items.*.weight' => ['nullable', 'numeric', 'min:0'],
            'shipment.items.*.volume' => ['nullable', 'numeric', 'min:0'],
            'shipment.items.*.value' => ['nullable', 'numeric', 'min:0'],
            'shipment.items.*.nfe_key' => ['nullable', 'string', 'max:60'],

            'proposal' => ['nullable', 'array'],
            'proposal.title' => ['nullable', 'string', 'max:255'],
            'proposal.valid_until' => ['nullable', 'date', 'after_or_equal:today'],
            'proposal.discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'proposal.notes' => ['nullable', 'string', 'max:5000'],

            'route' => ['nullable', 'array'],
            'route.auto_assign' => ['sometimes', 'boolean'],
            'route.driver_id' => ['nullable', 'integer', Rule::exists('drivers', 'id')],
            'route.scheduled_date' => ['nullable', 'date'],

            'notifications' => ['nullable', 'array'],
            'notifications.send_whatsapp' => ['sometimes', 'boolean'],
            'notifications.customer_phone' => ['required_if:notifications.send_whatsapp,true', 'nullable', 'string', 'max:30'],

            'metadata' => ['nullable', 'array'],
            'metadata.source' => ['nullable', 'string', 'max:255'],
            'metadata.conversation_id' => ['nullable', 'string', 'max:255'],
        ];
    }
}















