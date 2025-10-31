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
        Schema::create('workflow_sub_workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_workflow_id')->constrained('workflows')->onDelete('cascade');
            $table->foreignId('sub_workflow_id')->constrained('workflows')->onDelete('cascade');
            $table->foreignId('node_id')->nullable()->constrained('nodes')->onDelete('cascade');
            $table->string('name'); // reference name in parent workflow
            $table->json('parameters')->nullable(); // parameters to pass to sub-workflow
            $table->integer('order')->default(0); // execution order if multiple sub-workflows
            $table->timestamps();

            $table->unique(['parent_workflow_id', 'sub_workflow_id']);
            $table->index(['parent_workflow_id', 'order']);
            $table->index('sub_workflow_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_sub_workflows');
    }
};
