@php($codeLocked = $codeLocked ?? false)

<div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
    <div>
        <x-form.input name="product_code" label="Product code" :value="$product->product_code" required
                      maxlength="50" autocomplete="off" class="font-mono uppercase"
                      :readonly="$codeLocked"
                      :hint="$codeLocked
                          ? 'Locked because batches have been registered for this product.'
                          : 'Unique identifier, e.g. RW-MAIZE-25KG. Letters, numbers, dots, dashes and underscores.'" />
    </div>

    <x-form.input name="name" label="Product name" :value="$product->name" required maxlength="150" />

    <x-form.select name="category" label="Category" required
                   :value="$product->category?->value"
                   :options="collect($categories)->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all()" />

    <x-form.select name="unit_of_measure" label="Unit of measure" required
                   :value="$product->unit_of_measure?->value"
                   :options="collect($units)->mapWithKeys(fn ($u) => [$u->value => $u->label()])->all()" />

    <div class="sm:col-span-2">
        <x-form.input name="manufacturer_name" label="Manufacturer" :value="$product->manufacturer_name" required maxlength="150" />
    </div>

    <div class="sm:col-span-2">
        <x-form.textarea name="description" label="Description" :value="$product->description" maxlength="2000" />
    </div>
</div>
