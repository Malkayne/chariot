<?php

namespace App\Console\Commands;

use App\Models\Ride;
use Illuminate\Console\Command;

class CleanExpiredRides extends Command
{
    protected $signature   = 'rides:clean';
    protected $description = 'Mark rides whose departure time has passed as completed.';

    public function handle(): int
    {
        $count = Ride::whereIn('status', ['active', 'full'])
            ->where('departing_at', '<', now()->subHour())
            ->whereNotNull('departing_at')
            ->update(['status' => 'completed']);

        $this->info("Marked {$count} expired ride(s) as completed.");

        return Command::SUCCESS;
    }
}
