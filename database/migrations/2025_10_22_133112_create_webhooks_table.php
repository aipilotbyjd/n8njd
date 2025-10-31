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
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('workflows')->onDelete('cascade');
            $table->string('name')->nullable(); // human-readable name
            $table->string('path')->unique(); // webhook URL path (e.g., /webhook/abc123)
            $table->string('http_method')->default('POST'); // GET, POST, PUT, DELETE, PATCH
            $table->boolean('is_active')->default(true);
            $table->string('secret')->nullable(); // for signature validation
            $table->json('headers')->nullable(); // custom headers to send
            $table->text('description')->nullable();
            $table->integer('call_count')->default(0); // total calls received
            $table->timestamp('last_called_at')->nullable();
            $table->timestamps();

            $table->index('workflow_id');
            $table->index('path');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhooks');
    }
};
