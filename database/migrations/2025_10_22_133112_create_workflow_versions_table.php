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
        Schema::create('workflow_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('workflows')->onDelete('cascade');
            $table->integer('version_number');
            $table->json('workflow_data'); // serialized workflow (nodes, connections, settings)
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->text('change_notes')->nullable(); // description of changes
            $table->boolean('is_active')->default(false); // only one active version at a time
            $table->timestamps();

            $table->unique(['workflow_id', 'version_number']);
            $table->index(['workflow_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_versions');
    }
};
