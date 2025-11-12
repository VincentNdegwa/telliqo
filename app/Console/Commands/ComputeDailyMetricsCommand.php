<?php

namespace App\Console\Commands;

use App\Jobs\ComputeDailyMetrics;
use Illuminate\Console\Command;

class ComputeDailyMetricsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metrics:compute {--business= : Specific business ID} {--date= : Date to compute (Y-m-d)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compute daily metrics for businesses';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $businessId = $this->option('business');
        $date = $this->option('date');

        $this->info('Computing daily metrics...');

        ComputeDailyMetrics::dispatch($businessId, $date);

        $this->info('Metrics computation job dispatched successfully!');
        
        return Command::SUCCESS;
    }
}
