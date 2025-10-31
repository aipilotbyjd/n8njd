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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->onDelete('set null');
            $table->string('action'); // created, updated, deleted, executed, logged_in, etc.
            $table->string('entity_type'); // workflow, node, execution, user, organization
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('old_values')->nullable(); // previous values (for updates)
            $table->json('new_values')->nullable(); // new values (for creates/updates)
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('session_id')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['organization_id', 'created_at']);
            $table->index(['entity_type', 'entity_id']);
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
