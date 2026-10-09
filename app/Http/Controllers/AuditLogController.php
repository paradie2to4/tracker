<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', AuditLog::class);

        $event = trim((string) $request->query('event', ''));

        $logs = AuditLog::query()
            ->with(['user', 'subject'])
            ->when($event !== '', fn ($query) => $query->whereLike('event', "{$event}%"))
            ->latest('created_at')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('audit.index', [
            'logs' => $logs,
            'event' => $event,
            'eventGroups' => ['product', 'batch', 'stock', 'shipment', 'organization', 'location'],
        ]);
    }
}
