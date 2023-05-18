<?php

namespace Coderstm\Providers;

use Coderstm\Events\TaskCreated;
use Coderstm\Events\BookingCreated;
use Coderstm\Events\EnquiryCreated;
use Coderstm\Events\BookingCanceled;
use Coderstm\Events\ReferralCreated;
use Coderstm\Events\GuestPassCreated;
use Coderstm\Events\MembershipCreated;
use Coderstm\Events\UserStatusUpdated;
use Coderstm\Listeners\SendBookingCanceled;
use Illuminate\Auth\Events\Registered;
use Coderstm\Listeners\ProcessStandbyBooking;
use Coderstm\Listeners\SendBookingConfirmation;
use Coderstm\Listeners\Usages\AddClassesFeatureUsages;
use Coderstm\Listeners\Usages\RemoveClassesFeatureUsages;
use Coderstm\Listeners\Usages\AddGuestPassFeatureUsages;
use Coderstm\Listeners\SendEnquiryConfirmation;
use Coderstm\Listeners\SendEnquiryNotification;
use Coderstm\Listeners\SendReferralNotification;
use Laravel\Cashier\Events\WebhookReceived;
use Coderstm\Listeners\SendGuestPassNotification;
use Coderstm\Listeners\SendTaskUsersNotification;
use Coderstm\Events\Cashier\SubscriptionProcessed;
use Coderstm\Listeners\SendMembershipNotification;
use Coderstm\Listeners\Cashier\CashierEventListener;
use Coderstm\Listeners\CreateEnquiryMemberByReferral;
use Coderstm\Listeners\UserStatusUpdatedNotification;
use Coderstm\Listeners\CreateEnquiryMemberByGuestPass;
use Coderstm\Listeners\Cashier\SubscriptionEventListener;
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
