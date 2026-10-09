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
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            // RESTRICT: a product that has batches can never be deleted,
            // which protects the traceability history. PostgreSQL does not
            // index foreign keys automatically, so the index is explicit.
            $table->foreignId('product_id')->index()->constrained()->restrictOnDelete();
            $table->string('batch_number', 50)->unique();
            $table->date('manufacturing_date');
            $table->date('expiry_date')->nullable()->index();
            // NUMERIC(14,3): exact decimals (no floating-point rounding),
            // three decimal places for kg/litre quantities, and up to
            // 99,999,999,999.999 units per batch.
            $table->decimal('initial_quantity', 14, 3);
            $table->decimal('current_quantity', 14, 3);
            // Status is derived (see App\Enums\BatchStatus). Only the recall,
            // an explicit human decision, is stored.
            $table->timestamp('recalled_at')->nullable();
            $table->text('recall_reason')->nullable();
            $table->foreignId('recalled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement(<<<'SQL'
                ALTER TABLE batches
                    ADD CONSTRAINT batches_initial_quantity_positive CHECK (initial_quantity > 0),
                    ADD CONSTRAINT batches_current_quantity_non_negative CHECK (current_quantity >= 0),
                    ADD CONSTRAINT batches_current_within_initial CHECK (current_quantity <= initial_quantity),
                    ADD CONSTRAINT batches_expiry_after_manufacturing CHECK (expiry_date IS NULL OR expiry_date >= manufacturing_date),
                    ADD CONSTRAINT batches_recall_has_reason CHECK ((recalled_at IS NULL) = (recall_reason IS NULL))
                SQL);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
