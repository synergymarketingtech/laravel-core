<?php

namespace Coderstm\Core\Providers;

use Coderstm\Core\Events\TaskCreated;
use Coderstm\Core\Events\BookingCreated;
use Coderstm\Core\Events\EnquiryCreated;
use Coderstm\Core\Events\BookingCanceled;
use Coderstm\Core\Events\ReferralCreated;
use Coderstm\Core\Events\GuestPassCreated;
use Coderstm\Core\Events\MembershipCreated;
use Coderstm\Core\Events\UserStatusUpdated;
use Coderstm\Core\Listeners\SendBookingCanceled;
use Illuminate\Auth\Events\Registered;
use Coderstm\Core\Listeners\ProcessStandbyBooking;
use Coderstm\Core\Listeners\SendBookingConfirmation;
use Coderstm\Core\Listeners\Usages\AddClassesFeatureUsages;
use Coderstm\Core\Listeners\Usages\RemoveClassesFeatureUsages;
use Coderstm\Core\Listeners\Usages\AddGuestPassFeatureUsages;
use Coderstm\Core\Listeners\SendEnquiryConfirmation;
use Coderstm\Core\Listeners\SendEnquiryNotification;
use Coderstm\Core\Listeners\SendReferralNotification;
use Laravel\Cashier\Events\WebhookReceived;
use Coderstm\Core\Listeners\SendGuestPassNotification;
use Coderstm\Core\Listeners\SendTaskUsersNotification;
use Coderstm\Core\Events\Cashier\SubscriptionProcessed;
use Coderstm\Core\Listeners\SendMembershipNotification;
use Coderstm\Core\Listeners\Cashier\CashierEventListener;
use Coderstm\Core\Listeners\CreateEnquiryMemberByReferral;
use Coderstm\Core\Listeners\UserStatusUpdatedNotification;
use Coderstm\Core\Listeners\CreateEnquiryMemberByGuestPass;
use Coderstm\Core\Listeners\Cashier\SubscriptionEventListener;
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
