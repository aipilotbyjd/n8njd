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
        Schema::create('node_types', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // trigger, action, logic, transform, wait
            $table->string('node_type')->unique(); // webhook, http_request, if, etc.
            $table->string('name'); // Display name
            $table->text('description')->nullable();
            $table->string('icon')->default('box');
            $table->string('color')->default('#6366f1');
            $table->string('category'); // trigger, action, logic, data, database, communication, utility
            $table->json('inputs')->nullable(); // ['main'] or []
            $table->json('outputs')->nullable(); // ['main'] or ['true', 'false']
            $table->json('properties')->nullable(); // Schema for configuration form
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('type');
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('node_types');
    }
};
