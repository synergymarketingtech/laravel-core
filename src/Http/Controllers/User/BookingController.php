<?php

namespace Coderstm\Http\Controllers\User;

use Coderstm\Models\Booking;
use Coderstm\Events\BookingCanceled;
use Coderstm\Http\Controllers\Controller;

class BookingController extends Controller
{
    /**
     * Cancel the specified resource from storage.
     *
     * @param  \Coderstm\Models\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function cancel(Booking $booking)
    {
        $booking->cancel();
        event(new BookingCanceled($booking));
        return response()->json([
            'message' => 'Booking has been canceled successfully!',
        ], 200);
    }
}
