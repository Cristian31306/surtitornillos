<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\SystemExpirationNotification;
use Illuminate\Support\Facades\Log;

class CheckSystemExpiration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-system-expiration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the system membership expiration and send reminders 30, 15, and 7 days before.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get the expiration date from settings
        $expirationDateString = Setting::get('membership_expires_at');
        
        if (!$expirationDateString) {
            $this->warn('No membership_expires_at setting found.');
            return;
        }

        try {
            $expirationDate = Carbon::parse($expirationDateString)->startOfDay();
            $today = Carbon::now()->startOfDay();

            $daysRemaining = $today->diffInDays($expirationDate, false);

            $this->info("Days remaining until expiration: {$daysRemaining}");

            // Send notification 30 days, 15 days, and 7 days before
            if (in_array($daysRemaining, [30, 15, 7])) {
                $emails = ['durancristian31306@gmail.com', 'cadm31306@gmail.com'];
                
                foreach ($emails as $email) {
                    Mail::to($email)->send(new SystemExpirationNotification($daysRemaining, $expirationDate->format('Y-m-d')));
                    $this->info("Reminder sent to {$email} for {$daysRemaining} days remaining.");
                }
                
                Log::info("System expiration reminder sent. Days remaining: {$daysRemaining}. Emails: " . implode(', ', $emails));
            }

        } catch (\Exception $e) {
            $this->error('Error parsing expiration date or sending emails: ' . $e->getMessage());
            Log::error('CheckSystemExpiration error: ' . $e->getMessage());
        }
    }
}
