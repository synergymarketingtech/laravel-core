<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Enum\AppStatus;
use App\Notifications\HoldMemberNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class CheckHoldUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:hold';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check hold users';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        User::where('release_at', '<=', now())->each(function ($user) {
            $user->update([
                'status' => AppStatus::ACTIVE->value,
                'release_at' => null
            ]);

            Notification::route('mail', [
                'reception@pro-fit28.co.uk' => 'Reception'
            ])->notify(new HoldMemberNotification($user));
            $this->info("User #{$user->id} has been released!");
        });
    }
}
