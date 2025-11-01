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
        Schema::create('connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('workflows')->onDelete('cascade');
            $table->foreignId('source_node_id')->constrained('nodes')->onDelete('cascade');
            $table->foreignId('target_node_id')->constrained('nodes')->onDelete('cascade');
            $table->string('source_handle')->nullable();
            $table->string('target_handle')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();

            $table->index(['workflow_id', 'source_node_id']);
            $table->index(['workflow_id', 'target_node_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('connections');
    }
};
