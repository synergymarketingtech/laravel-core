<?php

namespace Coderstm\Tests\Feature;

use Carbon\Carbon;

class SubscriptionsTest extends FeatureTestCase
{
    /**
     * @var string
     */
    protected static $productId;

    /**
     * @var string
     */
    protected static $priceId;

    /**
     * @var string
     */
    protected static $otherPriceId;

    /**
     * @var string
     */
    protected static $premiumPriceId;

    /**
     * @var string
     */
    protected static $couponId;

    /**
     * @var string
     */
    protected static $taxRateId;

    public static function setUpBeforeClass(): void
    {
        if (!getenv('STRIPE_SECRET')) {
            return;
        }

        parent::setUpBeforeClass();

        static::$productId = self::stripe()->products->create([
            'name' => 'Laravel Core Test Product',
            'type' => 'service',
        ])->id;

        static::$priceId = self::stripe()->prices->create([
            'product' => static::$productId,
            'nickname' => 'Monthly $10',
            'currency' => 'USD',
            'recurring' => [
                'interval' => 'month',
            ],
            'billing_scheme' => 'per_unit',
            'unit_amount' => 1000,
        ])->id;

        static::$otherPriceId = self::stripe()->prices->create([
            'product' => static::$productId,
            'nickname' => 'Monthly $10 Other',
            'currency' => 'USD',
            'recurring' => [
                'interval' => 'month',
            ],
            'billing_scheme' => 'per_unit',
            'unit_amount' => 1000,
        ])->id;

        static::$premiumPriceId = self::stripe()->prices->create([
            'product' => static::$productId,
            'nickname' => 'Monthly $20 Premium',
            'currency' => 'USD',
            'recurring' => [
                'interval' => 'month',
            ],
            'billing_scheme' => 'per_unit',
            'unit_amount' => 2000,
        ])->id;

        static::$couponId = self::stripe()->coupons->create([
            'duration' => 'repeating',
            'amount_off' => 500,
            'duration_in_months' => 3,
            'currency' => 'USD',
        ])->id;

        static::$taxRateId = self::stripe()->taxRates->create([
            'display_name' => 'VAT',
            'description' => 'VAT Belgium',
            'jurisdiction' => 'BE',
            'percentage' => 21,
            'inclusive' => false,
        ])->id;
    }

    public function test_subscriptions_can_be_created()
    {
        $user = $this->createCustomer('subscriptions_can_be_created');

        // Create Subscription
        $user->newSubscription('main', static::$priceId)
            ->withMetadata($metadata = ['order_id' => '8'])
            ->create('pm_card_visa');

        $this->assertEquals(1, count($user->subscriptions));
        $this->assertNotNull(($subscription = $user->subscription('main'))->stripe_id);
        $this->assertSame($metadata, $subscription->asStripeSubscription()->metadata->toArray());

        $this->assertTrue($user->subscribed('main'));
        $this->assertTrue($user->subscribedToProduct(static::$productId, 'main'));
        $this->assertTrue($user->subscribedToPrice(static::$priceId, 'main'));
        $this->assertFalse($user->subscribedToPrice(static::$priceId, 'something'));
        $this->assertFalse($user->subscribedToPrice(static::$otherPriceId, 'main'));
        $this->assertTrue($user->subscribed('main', static::$priceId));
        $this->assertFalse($user->subscribed('main', static::$otherPriceId));
        $this->assertTrue($user->subscription('main')->active());
        $this->assertFalse($user->subscription('main')->canceled());
        $this->assertFalse($user->subscription('main')->onGracePeriod());
        $this->assertTrue($user->subscription('main')->recurring());
        $this->assertFalse($user->subscription('main')->ended());

        // Cancel Subscription
        $subscription = $user->subscription('main');
        $subscription->cancel();

        $this->assertTrue($subscription->active());
        $this->assertTrue($subscription->canceled());
        $this->assertTrue($subscription->onGracePeriod());
        $this->assertFalse($subscription->recurring());
        $this->assertFalse($subscription->ended());

        // Modify Ends Date To Past
        $oldGracePeriod = $subscription->ends_at;
        $subscription->fill(['ends_at' => Carbon::now()->subDays(5)])->save();

        $this->assertFalse($subscription->active());
        $this->assertTrue($subscription->canceled());
        $this->assertFalse($subscription->onGracePeriod());
        $this->assertFalse($subscription->recurring());
        $this->assertTrue($subscription->ended());

        $subscription->fill(['ends_at' => $oldGracePeriod])->save();

        // Resume Subscription
        $subscription->resume();

        $this->assertTrue($subscription->active());
        $this->assertFalse($subscription->canceled());
        $this->assertFalse($subscription->onGracePeriod());
        $this->assertTrue($subscription->recurring());
        $this->assertFalse($subscription->ended());

        // Increment & Decrement
        $subscription->incrementQuantity();

        $this->assertEquals(2, $subscription->quantity);

        $subscription->decrementQuantity();

        $this->assertEquals(1, $subscription->quantity);

        // Swap Price and invoice immediately.
        $subscription->swapAndInvoice(static::$otherPriceId);

        $this->assertEquals(static::$otherPriceId, $subscription->stripe_price);

        // Invoice Tests
        $invoice = $user->invoices()[1];

        $this->assertEquals('$10.00', $invoice->total());
        $this->assertFalse($invoice->hasDiscount());
        $this->assertFalse($invoice->hasStartingBalance());
        $this->assertEmpty($invoice->discounts());
        $this->assertInstanceOf(Carbon::class, $invoice->date());
    }

    public function test_upcoming_invoice()
    {
        $user = $this->createCustomer('subscription_upcoming_invoice');
        $subscription = $user->newSubscription('main', static::$priceId)
            ->create('pm_card_visa');

        $invoice = $subscription->previewInvoice(static::$otherPriceId);

        $this->assertSame('draft', $invoice->status);
        $this->assertSame(1000, $invoice->total);
    }

    public function test_invoice_subscription_directly()
    {
        $user = $this->createCustomer('invoice_subscription_directly');
        $subscription = $user->newSubscription('main', static::$priceId)
            ->create('pm_card_visa');

        $subscription->updateQuantity(3);

        $invoice = $subscription->invoice();

        $this->assertSame('paid', $invoice->status);
        $this->assertSame(2000, $invoice->total);
    }
}
