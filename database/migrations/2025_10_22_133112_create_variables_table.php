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
        Schema::create('variables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
            $table->foreignId('workflow_id')->nullable()->constrained('workflows')->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('key');
            $table->text('value');
            $table->enum('type', ['string', 'number', 'boolean', 'json', 'encrypted'])->default('string');
            $table->text('description')->nullable();
            $table->boolean('is_encrypted')->default(false);
            $table->timestamps();

            $table->unique(['organization_id', 'workflow_id', 'key']);
            $table->index('workflow_id');
            $table->index('key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variables');
    }
};
