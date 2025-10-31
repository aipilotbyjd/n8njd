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
        Schema::create('rate_limits', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g., user:123, ip:192.168.1.1, organization:456
            $table->string('type')->default('api'); // api, webhook, login, etc.
            $table->integer('attempts')->default(0);
            $table->integer('max_attempts')->default(60); // max attempts allowed
            $table->integer('decay_minutes')->default(1); // reset window in minutes
            $table->timestamp('locked_until')->nullable(); // when lock expires
            $table->timestamps();

            $table->index('key');
            $table->index('type');
            $table->index('locked_until');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rate_limits');
    }
};
