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
        Schema::table('executions', function (Blueprint $table) {
            // Skip org_id if it already exists
            if (!Schema::hasColumn('executions', 'org_id')) {
                $table->foreignId('org_id')->nullable()->constrained('organizations')->onDelete('cascade');
            }

            $table->json('trigger_data')->nullable()->after('triggered_by');
            $table->integer('execution_time_ms')->nullable()->after('finished_at');
            $table->text('error_stack')->nullable()->after('error_message');
            $table->integer('node_executions_count')->default(0)->after('execution_time_ms');
            $table->string('waiting_node_id')->nullable()->after('node_executions_count');
        });

        // Add indexes separately
        try {
            Schema::table('executions', function (Blueprint $table) {
                $table->index(['org_id', 'status'])->ifDoesntExist();
            });
        } catch (\Exception $e) {
            // Index might already exist, ignore
        }

        try {
            Schema::table('executions', function (Blueprint $table) {
                $table->index('triggered_by')->ifDoesntExist();
            });
        } catch (\Exception $e) {
            // Index might already exist, ignore
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('executions', function (Blueprint $table) {
            $table->dropIndexIfExists(['org_id', 'status']);
            $table->dropIndexIfExists('triggered_by');
        });

        Schema::table('executions', function (Blueprint $table) {
            $columnsToDrop = [
                'trigger_data',
                'execution_time_ms',
                'error_stack',
                'node_executions_count',
                'waiting_node_id',
            ];

            // Only drop org_id if we created it
            if (!in_array('org_id', ['trigger_data', 'execution_time_ms', 'error_stack', 'node_executions_count', 'waiting_node_id'])) {
                $columnsToDrop[] = 'org_id';
            }

            $table->dropColumn($columnsToDrop);
        });
    }
};
