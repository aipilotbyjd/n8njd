<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('node_executions', function (Blueprint $table) {
            $table->string('node_type')->nullable()->after('node_id');
            $table->foreignId('workflow_id')->nullable()->after('execution_id')->constrained('workflows')->onDelete('cascade');
            $table->integer('execution_time_ms')->nullable()->after('finished_at');
            $table->string('input_data_id')->nullable()->after('input');
            $table->string('output_data_id')->nullable()->after('output');
            $table->text('error')->nullable()->after('error_message');
        });
    }

    public function down(): void
    {
        Schema::table('node_executions', function (Blueprint $table) {
            $table->dropColumn(['node_type', 'workflow_id', 'execution_time_ms', 'input_data_id', 'output_data_id', 'error']);
        });
    }
};
