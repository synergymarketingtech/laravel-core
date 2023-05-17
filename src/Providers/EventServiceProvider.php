<?php

namespace CoderstmCore\Providers;

use CoderstmCore\Events\TaskCreated;
use CoderstmCore\Events\BookingCreated;
use CoderstmCore\Events\EnquiryCreated;
use CoderstmCore\Events\BookingCanceled;
use CoderstmCore\Events\ReferralCreated;
use CoderstmCore\Events\GuestPassCreated;
use CoderstmCore\Events\MembershipCreated;
use CoderstmCore\Events\UserStatusUpdated;
use CoderstmCore\Listeners\SendBookingCanceled;
use Illuminate\Auth\Events\Registered;
use CoderstmCore\Listeners\ProcessStandbyBooking;
use CoderstmCore\Listeners\SendBookingConfirmation;
use CoderstmCore\Listeners\Usages\AddClassesFeatureUsages;
use CoderstmCore\Listeners\Usages\RemoveClassesFeatureUsages;
use CoderstmCore\Listeners\Usages\AddGuestPassFeatureUsages;
use CoderstmCore\Listeners\SendEnquiryConfirmation;
use CoderstmCore\Listeners\SendEnquiryNotification;
use CoderstmCore\Listeners\SendReferralNotification;
use Laravel\Cashier\Events\WebhookReceived;
use CoderstmCore\Listeners\SendGuestPassNotification;
use CoderstmCore\Listeners\SendTaskUsersNotification;
use CoderstmCore\Events\Cashier\SubscriptionProcessed;
use CoderstmCore\Listeners\SendMembershipNotification;
use CoderstmCore\Listeners\Cashier\CashierEventListener;
use CoderstmCore\Listeners\CreateEnquiryMemberByReferral;
use CoderstmCore\Listeners\UserStatusUpdatedNotification;
use CoderstmCore\Listeners\CreateEnquiryMemberByGuestPass;
use CoderstmCore\Listeners\Cashier\SubscriptionEventListener;
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
