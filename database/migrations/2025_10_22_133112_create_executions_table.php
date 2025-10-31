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
        Schema::create('executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('workflows')->onDelete('cascade');
            $table->foreignId('triggered_by')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['pending', 'running', 'success', 'error', 'cancelled'])->default('pending');
            $table->enum('mode', ['manual', 'webhook', 'schedule'])->default('manual');
            $table->json('input_data')->nullable();
            $table->json('output_data')->nullable();
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable(); // execution metadata
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['workflow_id', 'status']);
            $table->index('triggered_by');
            $table->index('started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('executions');
    }
};
