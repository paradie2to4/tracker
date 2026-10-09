<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The stock ledger. Rows are append-only: a mistake is corrected with a
     * new movement, never by editing history. On PostgreSQL a trigger
     * rejects UPDATE and DELETE outright.
     */
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained()->restrictOnDelete();
            // App\Enums\MovementType
            $table->string('type', 30);
            $table->decimal('quantity', 14, 3);
            $table->foreignId('from_location_id')->nullable()->index()->constrained('locations')->restrictOnDelete();
            $table->foreignId('to_location_id')->nullable()->index()->constrained('locations')->restrictOnDelete();
            $table->foreignId('shipment_id')->nullable()->index()->constrained()->restrictOnDelete();
            // App\Enums\RemovalReason, for removals only.
            $table->string('removal_reason', 30)->nullable();
            $table->text('notes')->nullable();
            // Nullable for system-generated movements (e.g. demo seed data).
            // No ON DELETE action: a user who appears in the ledger cannot be deleted.
            $table->foreignId('user_id')->nullable()->index()->constrained()->restrictOnDelete();
            $table->timestamp('occurred_at');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['batch_id', 'occurred_at']);
        });

        if (DB::getDriverName() === 'pgsql') {
            // Each movement type affects exactly one location (see MovementType).
            DB::statement(<<<'SQL'
                ALTER TABLE stock_movements
                    ADD CONSTRAINT stock_movements_quantity_positive CHECK (quantity > 0),
                    ADD CONSTRAINT stock_movements_shape CHECK (
                        (type = 'production' AND from_location_id IS NULL AND to_location_id IS NOT NULL AND shipment_id IS NULL AND removal_reason IS NULL)
                        OR (type = 'dispatch' AND from_location_id IS NOT NULL AND to_location_id IS NULL AND shipment_id IS NOT NULL AND removal_reason IS NULL)
                        OR (type IN ('receipt', 'cancellation_return') AND from_location_id IS NULL AND to_location_id IS NOT NULL AND shipment_id IS NOT NULL AND removal_reason IS NULL)
                        OR (type = 'removal' AND from_location_id IS NOT NULL AND to_location_id IS NULL AND shipment_id IS NULL AND removal_reason IS NOT NULL)
                    )
                SQL);

            DB::statement(<<<'SQL'
                CREATE OR REPLACE FUNCTION reject_append_only_change() RETURNS trigger AS $$
                BEGIN
                    RAISE EXCEPTION 'Table % is append-only: % is not allowed', TG_TABLE_NAME, TG_OP
                        USING ERRCODE = 'restrict_violation';
                END;
                $$ LANGUAGE plpgsql
                SQL);

            DB::statement(<<<'SQL'
                CREATE TRIGGER stock_movements_append_only
                    BEFORE UPDATE OR DELETE ON stock_movements
                    FOR EACH ROW EXECUTE FUNCTION reject_append_only_change()
                SQL);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');

        // Also used by the audit_logs trigger, which has already been rolled
        // back by the time this runs (migrations roll back in reverse order).
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP FUNCTION IF EXISTS reject_append_only_change()');
        }
    }
};
