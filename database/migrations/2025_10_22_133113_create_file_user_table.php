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
        Schema::create('file_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('file_id'); // external file reference (S3 key, etc.)
            $table->string('filename');
            $table->string('mime_type');
            $table->bigInteger('size'); // file size in bytes
            $table->string('path'); // storage path or URL
            $table->string('disk')->default('local'); // storage disk
            $table->json('metadata')->nullable(); // additional file metadata
            $table->timestamps();

            $table->index('user_id');
            $table->index('file_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_user');
    }
};
