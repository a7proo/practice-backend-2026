<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;

class CompletePastBookings extends Command
{
    protected $signature = 'bookings:complete';
    protected $description = 'Завершает прошедшие бронирования';

    public function handle()
    {
        $count = Booking::where('status', 'active')
            ->where('end_time', '<', now())
            ->update(['status' => 'completed']);

        $this->info("Завершено бронирований: {$count}");
        return 0;
    }
}