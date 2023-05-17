<?php

namespace CoderstmCore\Http\Controllers\User;

use CoderstmCore\Models\Booking;
use CoderstmCore\Events\BookingCanceled;
use CoderstmCore\Http\Controllers\Controller;

class BookingController extends Controller
{
    /**
     * Cancel the specified resource from storage.
     *
     * @param  \CoderstmCore\Models\Booking  $booking
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
