<div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-form.input name="name" label="Organisation name" :value="$organization->name" required maxlength="150" />
    </div>

    <x-form.select name="type" label="Type" required
                   :value="$organization->type?->value"
                   :options="collect($types)->mapWithKeys(fn ($t) => [$t->value => $t->label()])->all()" />

    <x-form.input name="tin" label="TIN" :value="$organization->tin" inputmode="numeric" maxlength="11" autocomplete="off"
                  hint="Rwanda Revenue Authority Tax Identification Number (9 digits)." />

    <x-form.input type="email" name="contact_email" label="Contact email" :value="$organization->contact_email" maxlength="150" />

    <x-form.input type="tel" name="contact_phone" label="Contact phone" :value="$organization->contact_phone" maxlength="30"
                  placeholder="+250 788 123 456" />

    @if ($organization->exists)
        <div class="sm:col-span-2">
            <input type="hidden" name="is_active" value="0">
            <label class="flex items-center gap-2 text-sm text-ink-700">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $organization->is_active))
                       class="size-4 rounded border-ink-300 text-brand-600 focus:ring-brand-600">
                Active
            </label>
            <p class="mt-1 text-xs text-ink-500">Marks an organisation you no longer work with. Its history is kept. To stop shipments to or from it, also deactivate its locations.</p>
        </div>
    @endif
</div>
