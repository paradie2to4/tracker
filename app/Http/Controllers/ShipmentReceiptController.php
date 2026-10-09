<?php

namespace App\Http\Controllers;

use App\Actions\Shipments\ReceiveShipment;
use App\Models\Shipment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ShipmentReceiptController extends Controller
{
    public function __invoke(Request $request, Shipment $shipment, ReceiveShipment $receive): RedirectResponse
    {
        Gate::authorize('receive', $shipment);

        $receive->handle($shipment, $request->user());

        return redirect()
            ->route('shipments.show', $shipment)
            ->with('success', "Shipment {$shipment->reference} has been received. The stock is now at the destination.");
    }
}
