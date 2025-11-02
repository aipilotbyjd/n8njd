<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('executions', 'org_id') && !Schema::hasColumn('executions', 'organization_id')) {
            Schema::table('executions', function (Blueprint $table) {
                $table->renameColumn('org_id', 'organization_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('executions', 'organization_id') && !Schema::hasColumn('executions', 'org_id')) {
            Schema::table('executions', function (Blueprint $table) {
                $table->renameColumn('organization_id', 'org_id');
            });
        }
    }
};
