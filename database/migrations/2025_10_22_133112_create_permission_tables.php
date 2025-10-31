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
        // Permissions table
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // create-workflows, edit-workflows, delete-workflows, execute-workflows
            $table->string('guard_name')->default('web');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        // Roles table
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // owner, admin, editor, viewer
            $table->string('guard_name')->default('web');
            $table->text('description')->nullable();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['name', 'guard_name', 'organization_id']);
        });

        // Role permissions pivot
        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');
            $table->primary(['role_id', 'permission_id']);
        });

        // Model has roles (users in roles)
        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->string('model_type'); // App\Models\User
            $table->unsignedBigInteger('model_id');
            $table->index(['model_type', 'model_id']);
            $table->primary(['role_id', 'model_id', 'model_type']);
        });

        // Model has permissions (direct permissions to models)
        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_type', 'model_id']);
            $table->primary(['permission_id', 'model_id', 'model_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');
    }
};
