<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Nullable at the database level because batches registered before
     * Phase 6 have no location. New batches must supply one (validated in
     * StoreBatchRequest).
     */
    public function up(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->foreignId('origin_location_id')->nullable()->after('product_id')
                ->index()->constrained('locations')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->dropConstrainedForeignId('origin_location_id');
        });
    }
};
