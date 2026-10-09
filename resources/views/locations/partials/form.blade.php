<div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
    <x-form.input name="code" label="Location code" :value="$location->code" required maxlength="50" autocomplete="off"
                  class="font-mono uppercase" hint="Unique short code, e.g. KGL-WH-01." />

    <x-form.input name="name" label="Location name" :value="$location->name" required maxlength="150" />

    <x-form.select name="type" label="Type" required
                   :value="$location->type?->value"
                   :options="collect($types)->mapWithKeys(fn ($t) => [$t->value => $t->label()])->all()" />

    @php($selectedDistrict = old('district', $location->district))
    <div>
        <label for="district" class="block text-sm font-medium text-slate-700">
            District <span class="font-normal text-slate-400">(optional)</span>
        </label>
        <select id="district" name="district" class="form-control mt-1.5"
                @error('district') aria-invalid="true" aria-describedby="district-error" @enderror>
            <option value="">Select a district…</option>
            @foreach ($districts as $province => $provinceDistricts)
                <optgroup label="{{ $province }}">
                    @foreach ($provinceDistricts as $district)
                        <option value="{{ $district }}" @selected($selectedDistrict === $district)>{{ $district }}</option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
        @error('district')
            <p id="district-error" class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <x-form.input name="address" label="Address" :value="$location->address" maxlength="255"
                      placeholder="e.g. KK 15 Rd, Kicukiro" />
    </div>

    @if ($location->exists)
        <div class="sm:col-span-2">
            <input type="hidden" name="is_active" value="0">
            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $location->is_active))
                       class="size-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-600">
                Active
            </label>
            <p class="mt-1 text-xs text-slate-500">Inactive locations cannot send or receive new shipments. Stock already there remains on record.</p>
        </div>
    @endif
</div>
