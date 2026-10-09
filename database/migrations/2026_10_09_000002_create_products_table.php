<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            // Codes are normalised to upper case before saving, so this
            // unique index is effectively case-insensitive.
            $table->string('product_code', 50)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            // Values come from App\Enums\ProductCategory / UnitOfMeasure.
            // Stored as strings (not native enums) so adding a value later
            // does not require an ALTER TYPE migration.
            $table->string('category', 50)->index();
            $table->string('manufacturer_name', 150);
            $table->string('unit_of_measure', 20);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
