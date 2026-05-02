<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ActivityLog;
use App\Models\Setting;
use Carbon\Carbon;

class PruneActivityLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:prune';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune old activity logs based on retention setting';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = Setting::where('key', 'logs_retention_days')->first()?->value ?? 30;
        
        $count = ActivityLog::where('created_at', '<', Carbon::now()->subDays($days))->delete();

        $this->info("Deleted {$count} old activity logs (Retention: {$days} days).");
    }
}
