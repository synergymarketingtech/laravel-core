<?php

namespace Coderstm\Core\Http\Controllers\User;

use Coderstm\Core\Models\Booking;
use Coderstm\Core\Events\BookingCanceled;
use Coderstm\Core\Http\Controllers\Controller;

class BookingController extends Controller
{
    /**
     * Cancel the specified resource from storage.
     *
     * @param  \Coderstm\Core\Models\Booking  $booking
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
