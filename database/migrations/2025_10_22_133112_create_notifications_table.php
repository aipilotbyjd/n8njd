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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type'); // execution_failed, workflow_completed, workflow_error, etc.
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // additional notification data
            $table->foreignId('triggered_by')->nullable()->constrained('users')->onDelete('set null');
            $table->unsignedBigInteger('entity_id')->nullable(); // workflow_id, execution_id, etc.
            $table->string('entity_type')->nullable(); // workflow, execution, etc.
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_read', 'created_at']);
            $table->index(['entity_type', 'entity_id']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
