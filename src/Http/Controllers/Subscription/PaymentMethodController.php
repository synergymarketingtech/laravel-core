<?php

namespace App\Http\Controllers\Subscription;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return response()->json($this->user()->getPaymentMethods());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'payment_method' => 'required',
            'last_four_digit' => 'required',
        ]);

        $user = $this->user();
        $paymentMethod = $request->input('payment_method');

        if ($user->checkPaymentMethod($request->input('last_four_digit'))) {
            return abort(403, 'Payment method already exists in your account.');
        }

        // create or get stripe customer
        $user->createOrGetStripeCustomer();

        if ($request->boolean('is_deafult')) {
            $user->updateDefaultPaymentMethod($paymentMethod);
            $user->updateDefaultPaymentMethodFromStripe();
        } else {
            $user->addPaymentMethod($paymentMethod);
        }

        return response()->json(['message' => "You have successfully added new payment method."]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $paymentMethod
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $paymentMethod)
    {
        $user = $this->user();

        try {
            $user->updateDefaultPaymentMethod($paymentMethod);
        } catch (\Throwable $th) {
            throw $th;
        }

        return response()->json(['message' => "You have successfully updated default payment method."]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $paymentMethod
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $paymentMethod)
    {
        $user = $this->user();

        try {
            $paymentMethod = $user->findPaymentMethod($paymentMethod);
            $paymentMethod->delete();
        } catch (\Throwable $th) {
            throw $th;
        }

        return response()->json(['message' => "You have successfully removed payment method."]);
    }

    private function user()
    {
        if (request()->filled('user_id') && is_admin()) {
            return User::findOrFail(request()->user_id);
        }
        return currentUser();
    }
}
