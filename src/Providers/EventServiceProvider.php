<?php

namespace App\Providers;

use App\Events\TaskCreated;
use App\Events\BookingCreated;
use App\Events\EnquiryCreated;
use App\Events\BookingCanceled;
use App\Events\ReferralCreated;
use App\Events\GuestPassCreated;
use App\Events\MembershipCreated;
use App\Events\UserStatusUpdated;
use App\Listeners\SendBookingCanceled;
use Illuminate\Auth\Events\Registered;
use App\Listeners\ProcessStandbyBooking;
use App\Listeners\SendBookingConfirmation;
use App\Listeners\Usages\AddClassesFeatureUsages;
use App\Listeners\Usages\RemoveClassesFeatureUsages;
use App\Listeners\Usages\AddGuestPassFeatureUsages;
use App\Listeners\SendEnquiryConfirmation;
use App\Listeners\SendEnquiryNotification;
use App\Listeners\SendReferralNotification;
use Laravel\Cashier\Events\WebhookReceived;
use App\Listeners\SendGuestPassNotification;
use App\Listeners\SendTaskUsersNotification;
use App\Events\Cashier\SubscriptionProcessed;
use App\Listeners\SendMembershipNotification;
use App\Listeners\Cashier\CashierEventListener;
use App\Listeners\CreateEnquiryMemberByReferral;
use App\Listeners\UserStatusUpdatedNotification;
use App\Listeners\CreateEnquiryMemberByGuestPass;
use App\Listeners\Cashier\SubscriptionEventListener;
use RachidLaasri\LaravelInstaller\Events\EnvironmentSaved;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        EnvironmentSaved::class => [
            ProcessEnvironment::class,
        ],
        EnquiryCreated::class => [
            SendEnquiryNotification::class,
            SendEnquiryConfirmation::class,
        ],
        TaskCreated::class => [
            SendTaskUsersNotification::class,
        ],
        BookingCreated::class => [
            SendBookingConfirmation::class,
            AddClassesFeatureUsages::class,
        ],
        BookingCanceled::class => [
            SendBookingCanceled::class,
            ProcessStandbyBooking::class,
            RemoveClassesFeatureUsages::class,
        ],
        GuestPassCreated::class => [
            CreateEnquiryMemberByGuestPass::class,
            SendGuestPassNotification::class,
            AddGuestPassFeatureUsages::class,
        ],
        ReferralCreated::class => [
            CreateEnquiryMemberByReferral::class,
            SendReferralNotification::class,
        ],
        MembershipCreated::class => [
            SendMembershipNotification::class,
        ],
        UserStatusUpdated::class => [
            UserStatusUpdatedNotification::class,
        ],
        WebhookReceived::class => [
            CashierEventListener::class,
        ],
        SubscriptionProcessed::class => [
            SubscriptionEventListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
