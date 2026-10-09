<?php

namespace App\Http\Controllers;

use App\Actions\Shipments\CancelShipment;
use App\Http\Requests\CancelShipmentRequest;
use App\Models\Shipment;
use Illuminate\Http\RedirectResponse;

class ShipmentCancellationController extends Controller
{
    public function __invoke(CancelShipmentRequest $request, Shipment $shipment, CancelShipment $cancel): RedirectResponse
    {
        $cancel->handle($shipment, $request->validated('cancellation_reason'), $request->user());

        return redirect()
            ->route('shipments.show', $shipment)
            ->with('success', "Shipment {$shipment->reference} has been cancelled. The stock has been returned to the origin.");
    }
}
