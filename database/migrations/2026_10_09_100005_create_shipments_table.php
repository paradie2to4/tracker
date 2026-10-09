<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_location_id')->index()->constrained('locations')->restrictOnDelete();
            $table->foreignId('to_location_id')->index()->constrained('locations')->restrictOnDelete();
            // App\Enums\ShipmentStatus
            $table->string('status', 20)->index();
            $table->text('notes')->nullable();

            $table->timestamp('dispatched_at');
            $table->foreignId('dispatched_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('received_at')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('shipment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->restrictOnDelete();
            $table->foreignId('batch_id')->index()->constrained()->restrictOnDelete();
            $table->decimal('quantity', 14, 3);
            $table->timestamps();

            $table->unique(['shipment_id', 'batch_id']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement(<<<'SQL'
                ALTER TABLE shipments
                    ADD CONSTRAINT shipments_distinct_locations CHECK (from_location_id <> to_location_id),
                    ADD CONSTRAINT shipments_status_check CHECK (status IN ('in_transit', 'received', 'cancelled')),
                    ADD CONSTRAINT shipments_received_consistency CHECK ((status = 'received') = (received_at IS NOT NULL AND received_by IS NOT NULL)),
                    ADD CONSTRAINT shipments_cancelled_consistency CHECK ((status = 'cancelled') = (cancelled_at IS NOT NULL AND cancelled_by IS NOT NULL AND cancellation_reason IS NOT NULL))
                SQL);

            DB::statement('ALTER TABLE shipment_items ADD CONSTRAINT shipment_items_quantity_positive CHECK (quantity > 0)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipment_items');
        Schema::dropIfExists('shipments');
    }
};
