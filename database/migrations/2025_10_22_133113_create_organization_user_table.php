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
        Schema::create('organization_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('role')->default('member'); // owner, admin, member, viewer
            $table->json('permissions')->nullable(); // granular permissions
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('invited_at')->nullable();
            $table->foreignId('invited_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->unique(['organization_id', 'user_id']);
            $table->index('role');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_user');
    }
};
