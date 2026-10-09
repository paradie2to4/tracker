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
     * Append-only record of who changed what and when. Enforced in the
     * model (App\Models\AuditLog) and, on PostgreSQL, by a trigger that uses
     * the reject_append_only_change() function created with stock_movements.
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            // e.g. "product.created", "shipment.dispatched"
            $table->string('event', 60)->index();
            $table->nullableMorphs('subject');
            $table->foreignId('user_id')->nullable()->index()->constrained()->restrictOnDelete();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement(<<<'SQL'
                CREATE TRIGGER audit_logs_append_only
                    BEFORE UPDATE OR DELETE ON audit_logs
                    FOR EACH ROW EXECUTE FUNCTION reject_append_only_change()
                SQL);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Dropping the table also drops its trigger. The shared function is
        // dropped by the stock_movements migration, which rolls back next.
        Schema::dropIfExists('audit_logs');
    }
};
