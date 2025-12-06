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
        Schema::table('node_types', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable()->after('is_active');
            $table->unsignedBigInteger('created_by')->nullable()->after('organization_id');
            $table->boolean('is_custom')->default(false)->after('created_by');
            
            $table->index('organization_id');
            $table->index('is_custom');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('node_types', function (Blueprint $table) {
            $table->dropIndex(['organization_id']);
            $table->dropIndex(['is_custom']);
            $table->dropColumn(['organization_id', 'created_by', 'is_custom']);
        });
    }
};
