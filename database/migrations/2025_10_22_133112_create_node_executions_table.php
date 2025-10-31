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
        Schema::create('node_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('execution_id')->constrained('executions')->onDelete('cascade');
            $table->foreignId('node_id')->constrained('nodes')->onDelete('cascade');
            $table->enum('status', ['pending', 'running', 'success', 'error', 'skipped'])->default('pending');
            $table->json('input')->nullable();
            $table->json('output')->nullable();
            $table->text('error_message')->nullable();
            $table->json('error_details')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->integer('execution_order')->default(0);
            $table->integer('retry_count')->default(0);
            $table->timestamps();

            $table->index(['execution_id', 'execution_order']);
            $table->index(['node_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('node_executions');
    }
};
