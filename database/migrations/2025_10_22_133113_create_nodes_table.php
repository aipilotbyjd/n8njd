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
        Schema::create('nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('workflows')->onDelete('cascade');
            $table->string('type'); // trigger, action, condition
            $table->string('node_type'); // webhook, http_request, email, etc.
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('position'); // { x: number, y: number }
            $table->json('configuration')->nullable(); // node-specific config
            $table->json('credentials')->nullable(); // encrypted credentials
            $table->integer('position_index')->default(0); // order in workflow
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['workflow_id', 'position_index']);
            $table->index('type');
            $table->index('node_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nodes');
    }
};
