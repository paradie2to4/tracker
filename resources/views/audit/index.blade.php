<x-layouts.app title="Audit log">
    <form method="GET" action="{{ route('audit.index') }}" role="search" class="card mb-6 flex flex-col gap-3 p-4 sm:flex-row sm:items-end">
        <div class="sm:w-64">
            <label for="event" class="block text-sm font-medium text-ink-700">Event type</label>
            <select id="event" name="event" class="form-control mt-1.5">
                <option value="">All events</option>
                @foreach ($eventGroups as $group)
                    <option value="{{ $group }}." @selected($event === $group.'.')>{{ ucfirst($group) }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary">Filter</button>
            @if ($event !== '')
                <a href="{{ route('audit.index') }}" class="btn btn-secondary">Clear</a>
            @endif
        </div>
    </form>

    <p class="mb-4 text-sm text-ink-500">An append-only record of every change. Entries cannot be edited or deleted, including by administrators.</p>

    <div class="card">
        @if ($logs->isEmpty())
            <x-empty-state title="No audit entries" />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-ink-200">
                    <thead class="bg-ink-50">
                        <tr>
                            <th scope="col" class="table-header">When</th>
                            <th scope="col" class="table-header">Event</th>
                            <th scope="col" class="table-header">Record</th>
                            <th scope="col" class="table-header">User</th>
                            <th scope="col" class="table-header">Changes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100">
                        @foreach ($logs as $log)
                            <tr class="align-top">
                                <td class="table-cell">
                                    <time datetime="{{ $log->created_at->toIso8601String() }}">{{ $log->created_at->format('d M Y, H:i:s') }}</time>
                                    @if ($log->ip_address)
                                        <span class="block text-xs text-ink-500">{{ $log->ip_address }}</span>
                                    @endif
                                </td>
                                <td class="table-cell font-mono text-xs">{{ $log->event }}</td>
                                <td class="table-cell">{{ $log->subject_type ? ucfirst($log->subject_type).' #'.$log->subject_id : '—' }}</td>
                                <td class="table-cell">{{ $log->user?->name ?? 'System' }}</td>
                                <td class="px-4 py-3 text-xs text-ink-600">
                                    @foreach ($log->new_values ?? [] as $field => $value)
                                        <div class="max-w-md truncate">
                                            <span class="font-medium text-ink-700">{{ $field }}:</span>
                                            @if (is_array($log->old_values) && array_key_exists($field, $log->old_values))
                                                <span class="text-ink-400 line-through">{{ App\Models\AuditLog::displayValue($log->old_values[$field]) }}</span> →
                                            @endif
                                            {{ App\Models\AuditLog::displayValue($value) }}
                                        </div>
                                    @endforeach
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($logs->hasPages())
                <div class="border-t border-ink-200 px-4 py-3">{{ $logs->links() }}</div>
            @endif
        @endif
    </div>
</x-layouts.app>
