<?php

namespace App\Console\Commands;

use App\Models\Schedule;
use App\Services\Execution\ExecutionService;
use Cron\CronExpression;
use Illuminate\Console\Command;

class RunScheduledWorkflows extends Command
{
    protected $signature = 'workflows:run-scheduled';

    protected $description = 'Execute workflows that are scheduled to run based on their cron expressions';

    protected $executionService;

    public function __construct(ExecutionService $executionService)
    {
        parent::__construct();
        $this->executionService = $executionService;
    }

    public function handle()
    {
        $schedules = Schedule::where('active', true)->get();

        if ($schedules->isEmpty()) {
            $this->info('No active schedules found.');

            return 0;
        }

        $this->info("Found {$schedules->count()} active schedule(s).");

        $executed = 0;

        foreach ($schedules as $schedule) {
            try {
                $cron = new CronExpression($schedule->cron_expression);
                $now = now($schedule->timezone ?? config('app.timezone'));

                if ($cron->isDue($now->toDateTimeString())) {
                    $this->info("Executing workflow: {$schedule->workflow->name} (ID: {$schedule->workflow_id})");

                    $this->executionService->executeWorkflow(
                        $schedule->workflow_id,
                        $schedule->workflow->organization_id,
                        $schedule->created_by,
                        $schedule->execution_data ?? [],
                        'schedule'
                    );

                    $schedule->last_execution_at = now();
                    $schedule->next_execution_at = $cron->getNextRunDate()->format('Y-m-d H:i:s');
                    $schedule->save();

                    $executed++;
                }
            } catch (\Exception $e) {
                $this->error("Error executing schedule {$schedule->id}: {$e->getMessage()}");
            }
        }

        $this->info("Executed {$executed} workflow(s).");

        return 0;
    }
}
