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
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('workflow_data'); // template workflow structure
            $table->string('category')->nullable(); // e.g., marketing, sales, development
            $table->string('image_url')->nullable(); // template preview image
            $table->integer('usage_count')->default(0); // how many times used
            $table->boolean('is_public')->default(false); // public template library
            $table->json('tags')->nullable(); // array of tag names
            $table->timestamps();

            $table->index('organization_id');
            $table->index(['is_public', 'category']);
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
