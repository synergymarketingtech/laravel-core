<?php

namespace Coderstm\Commands;

use Coderstm\Enum\AppStatus;
use Illuminate\Console\Command;
use Coderstm\Models\Cashier\Subscription;
use Coderstm\Models\Invoice;

class SubscriptionsInvoice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coderstm:subscriptions-invoices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync invoices from stripe for current subscriptions';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Subscription::query()->whereRaw('LENGTH(stripe_id) = ?', [28])->each(function ($subscription) {
            try {
                foreach ($subscription->invoices()->reverse() as $invoice) {
                    Invoice::createFromStripe($invoice, [
                        'subscription_id' => $subscription->id
                    ]);
                }
                $this->info("[Subscription #{$subscription->id}]: Invoices has been synced!");
            } catch (\Exception $ex) {
                report($ex);
                $this->error("[Subscription #{$subscription->id}]: {$ex->getMessage()}");
            }
        });
    }
}
