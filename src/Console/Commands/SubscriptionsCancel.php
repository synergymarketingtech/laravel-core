<?php

namespace App\Console\Commands;

use App\Enum\AppStatus;
use Illuminate\Console\Command;
use App\Models\Cashier\Subscription;

class SubscriptionsCancel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:cancel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel the subscription when it has cancels at';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Subscription::query()->active()->where('cancels_at', '<=', now())->each(function ($subscription) {
            try {
                $subscription->cancelNow();
                $subscription->user()->update([
                    'status' => AppStatus::DEACTIVE->value
                ]);
                $this->info("User #{$subscription->user()->id} has been deactivated!");
            } catch (\Exception $ex) {
                report($ex);
                $this->error("User #{$subscription->user()->id} unable to deactivated! {$ex->getMessage()}");
            }
        });
    }
}
