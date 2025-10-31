<?php

namespace App\Console\Commands;

use App\Models\WorkflowExecution;
use Illuminate\Console\Command;

class CleanupOldExecutions extends Command
{
    protected $signature = 'workflows:cleanup-executions {--days=30 : Number of days to keep executions}';

    protected $description = 'Delete workflow executions older than specified days';

    public function handle()
    {
        $days = $this->option('days');

        $this->info("Cleaning up executions older than {$days} days...");

        $count = WorkflowExecution::where('created_at', '<', now()->subDays($days))
            ->delete();

        $this->info("Deleted {$count} execution(s).");

        return 0;
    }
}
