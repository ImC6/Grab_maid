<?php

namespace App\Console\Commands;

use App\Models\BookingSession;
use Illuminate\Console\Command;
use Carbon\Carbon;

class RemoveBookingSession extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remove:booking-session';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove Booking Session that is created more than 10 minutes';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $bookingSessions = BookingSession::where('updated_at', '<=', Carbon::now()->subHour())->delete();

        if ($bookingSessions) {
            $this->info('Booking Session is removed at ' . Carbon::now()->format('Y-m-d H:i:s'));
        }
    }
}
