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
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->onDelete('cascade');
            $table->string('event_type'); // workflow_executed, execution_failed, workflow_error, user_invited, etc.
            $table->json('channels'); // array of notification channel IDs to use
            $table->boolean('is_enabled')->default(true);
            $table->json('conditions')->nullable(); // specific conditions for this notification
            $table->timestamps();

            $table->unique(['user_id', 'event_type']);
            $table->index('organization_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
