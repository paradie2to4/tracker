{{-- One human-readable line describing a stock movement. Requires fromLocation, toLocation and shipment.{fromLocation,toLocation} to be eager-loaded. --}}
@props(['movement'])

@php($shipment = $movement->shipment)

@switch($movement->type)
    @case(App\Enums\MovementType::Production)
        Produced at <span class="font-medium">{{ $movement->toLocation->name }}</span>
        @break
    @case(App\Enums\MovementType::Dispatch)
        Dispatched from <span class="font-medium">{{ $movement->fromLocation->name }}</span>
        to {{ $shipment->toLocation->name }}
        on <a href="{{ route('shipments.show', $shipment) }}" class="link font-mono">{{ $shipment->reference }}</a>
        @break
    @case(App\Enums\MovementType::Receipt)
        Received at <span class="font-medium">{{ $movement->toLocation->name }}</span>
        from {{ $shipment->fromLocation->name }}
        on <a href="{{ route('shipments.show', $shipment) }}" class="link font-mono">{{ $shipment->reference }}</a>
        @break
    @case(App\Enums\MovementType::CancellationReturn)
        Returned to <span class="font-medium">{{ $movement->toLocation->name }}</span>
        (<a href="{{ route('shipments.show', $shipment) }}" class="link font-mono">{{ $shipment->reference }}</a> cancelled)
        @break
    @case(App\Enums\MovementType::Removal)
        Removed at <span class="font-medium">{{ $movement->fromLocation->name }}</span>
        — {{ $movement->removal_reason->label() }}
        @break
@endswitch
