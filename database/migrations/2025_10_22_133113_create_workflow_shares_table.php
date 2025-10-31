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
        Schema::create('workflow_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('workflows')->onDelete('cascade');
            $table->foreignId('shared_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('shared_with_user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('shared_with_organization_id')->nullable()->constrained('organizations')->onDelete('cascade');
            $table->string('permission')->default('view'); // view, edit, execute
            $table->text('message')->nullable(); // sharing message
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('workflow_id');
            $table->index('shared_with_user_id');
            $table->index('shared_with_organization_id');
            $table->index('permission');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_shares');
    }
};
