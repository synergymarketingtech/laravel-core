<?php

namespace Coderstm\Http\Controllers\Subscription;

use Coderstm\Models\Plan;
use Coderstm\Models\User;
use Coderstm\Traits\Helpers;
use Stripe\Subscription;
use Coderstm\Models\Plan\Price;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Payment;
use Coderstm\Http\Controllers\Controller;
use Laravel\Cashier\Exceptions\IncompletePayment;

class SubscriptionController extends Controller
{
    use Helpers;

    public function index(Request $request)
    {
        $user = $this->user();

        $subscription = $user->subscription();
        if ($user->is_free_forever || !$subscription) {
            return response()->json([
                'message' => 'To access exclusive gym features, please subscribe to a plan. You are not currently subscribed to any plan.',
                'upcomingInvoice' => false,
                'defaultPaymentMethod' => null
            ], 200);
        }

        $upcomingInvoice = $subscription->upcomingInvoice();
        $subscription['defaultPaymentMethod'] = $user->default_payment_method ?? null;

        if ($subscription->canceled() && $subscription->onGracePeriod()) {
            $subscription['message'] = "You have cancelled your subscription. Your subscription will end on {$subscription->ends_at->format('D d M Y')}";
        } else if ($subscription->pastDue() || $user->hasIncompletePayment()) {
            $invoice = $subscription->latestInvoice();
            $subscription['message'] = "To activate your subscription, please complete payment of {$invoice->realTotal()}.";
        } else if ($upcomingInvoice) {
            $subscription['upcomingInvoice'] =  [
                'amount' => $upcomingInvoice->total(),
                'date' => $upcomingInvoice->date()->toFormattedDateString(),
            ];
            $subscription['message'] = "Next invoice {$subscription['upcomingInvoice']['amount']} on {$subscription['upcomingInvoice']['date']}";
        }
        return response()->json($subscription, 200);
    }

    public function subscribe(Request $request)
    {
        $request->validate([
            'payment_method' => 'required',
            'plan' => 'required',
        ]);

        if ($request->input('payment_method') == 'manual') {
            $request->merge([
                'payment_method' => null
            ]);
        }

        $user = $this->user();
        $payment_method = $request->input('payment_method');
        $payment_interval = optional($request)->payment_interval ?? 'month';
        $price = Price::planById($request->input('plan'), $payment_interval);
        $planID = $price->stripe_id;
        $isSubscribed = $user->subscribed();
        $subscription = null;

        if ($isSubscribed && $user->subscription()->stripe_price == $planID) {
            abort(403, "You already subscribed to {$price->plan->label} plan.");
        }

        try {
            if ($isSubscribed) {
                $subscription = $user->subscription()->swapAndInvoice($planID);
            } else {
                $subscription = $user->newSubscription('default', $planID)
                    ->create($payment_method);
            }
        } catch (IncompletePayment $exception) {
            $paymentIntents = $this->paymentIntents($exception->payment->id);
            if ($paymentIntents['paymentIntent']['status'] != 'requires_payment_method') {
                return $paymentIntents;
            }
        } finally {
            $this->updateUserPlan($price->plan_id);
        }

        return response()->json([
            'subscription' => $subscription,
            'message' => !$payment_method ? "Please contact our reception to make payment and complete your subscription!" : "You have successfully subscribe to {$price->plan->label} plan."
        ]);
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'payment_intent' => 'required',
            'plan' => 'required',
        ]);

        $user = $this->user();
        $payment_intent = $request->input('payment_intent');
        $plan = Plan::find($request->plan);

        try {
            $payment = new Payment(
                Cashier::stripe()->paymentIntents->retrieve(
                    $payment_intent,
                    ['expand' => ['payment_method']]
                )
            );
            if ($payment->isSucceeded()) {
                // confirm the subscription
                $subscription = $user->subscription();
                $subscription->stripe_status = Subscription::STATUS_ACTIVE;
                $subscription->save();
            } else {
                abort(403, 'We are unable to authenticate your payment method. Please choose a different payment method or try again.');
            }
        } catch (\Throwable $th) {
            throw $th;
        } finally {
            $this->updateUserPlan($plan->id);
        }

        return response()->json(['subscription' => $user->subscription(), 'message' => "You have successfully subscribe to {$plan->label} plan."]);
    }

    public function cancel(Request $request)
    {
        $this->user()->subscription()->cancel();

        return response()->json([
            'message' => 'You have successfully cancelled your subscription.'
        ], 200);
    }

    public function resume(Request $request)
    {
        $this->user()->subscription()->resume();

        return response()->json([
            'message' => 'You have successfully resume your subscription.'
        ], 200);
    }

    public function invoices(Request $request)
    {
        return response()->json($this->user()->appInvoices()->orderByDesc('created_at')->paginate($request->rowsPerPage ?: 10), 200);
    }

    public function downloadInvoice(Request $request, $invoiceId)
    {
        $user = $this->user();
        return $user->downloadInvoice($invoiceId, [
            'vendor' => config('app.name'),
            // 'product' => Str::title("{$user->plan->label} plan"),
        ], 'my-invoice');
    }

    /**
     * Creates an intent for payment so we can capture the payment
     * method for the user.
     *
     * @param Request $request The request data from the user.
     */
    public function getSetupIntent(Request $request)
    {
        return $this->user()->createSetupIntent();
    }

    private function paymentIntents($id)
    {
        $payment = new Payment(
            Cashier::stripe()->paymentIntents->retrieve(
                $id,
                ['expand' => ['payment_method']]
            )
        );

        $paymentIntent = Arr::only($payment->asStripePaymentIntent()->toArray(), [
            'id', 'status', 'payment_method_types', 'client_secret', 'payment_method',
        ]);

        $paymentIntent['payment_method'] = Arr::only($paymentIntent['payment_method'] ?? [], 'id');

        return [
            'amount' => $payment->amount(),
            'payment' => $payment,
            'paymentIntent' => array_filter($paymentIntent),
            'paymentMethod' => (string) optional($payment->payment_method)->type,
            'customer' => $payment->customer(),
            'requiresAction' => true,
        ];
    }

    //update user plan
    private function updateUserPlan($planID)
    {
        $this->user()->update([
            'plan_id' => $planID
        ]);
    }

    private function user()
    {
        if (request()->filled('user_id') && is_admin()) {
            return User::findOrFail(request()->user_id);
        }
        return currentUser();
    }
}
